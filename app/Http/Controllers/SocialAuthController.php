<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use App\SocialProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $intent = $request->get('intent', 'login'); // 'login' or 'link'
        $isPopup = $request->boolean('popup', false);
        $stateData = ['intent' => $intent];

        // If linking, include user ID in state to restore session after OAuth redirect
        if ($intent === 'link') {
            if (Auth::check()) {
                $stateData['user_id'] = Auth::id();
            } else {
                // If no session but linking is requested, redirect to login
                return redirect()->route('login')
                    ->with('error', __('auth.oauth.must_be_logged_in_to_link'));
            }
        }

        // Include popup flag in state
        if ($isPopup) {
            $stateData['popup'] = true;
        }

        Log::info('Social auth redirect', [
            'provider' => $provider,
            'intent' => $intent,
            'user_id' => $stateData['user_id'] ?? null,
            'is_popup' => $isPopup,
        ]);

        $stateString = base64_encode(json_encode($stateData));
        $response = Socialite::driver($provider)
            ->with(['state' => $stateString])
            ->redirect();

        // Socialite stores its own random state in the session; we must store our
        // custom state so the callback validation matches (InvalidStateException).
        $request->session()->put('state', $stateString);

        return $response;
    }

    /**
     * Obtain the user information from the OAuth provider.
     */
    public function callback(Request $request, string $provider): RedirectResponse|Response
    {
        $this->validateProvider($provider);

        try {
            $state = $request->get('state');
            $intent = 'login';
            $userId = null;
            $isPopup = false;

            if ($state) {
                $decoded = json_decode(base64_decode($state), true);
                $intent = $decoded['intent'] ?? 'login';
                $userId = $decoded['user_id'] ?? null;
                $isPopup = $decoded['popup'] ?? false;

                Log::info('Social auth callback', [
                    'provider' => $provider,
                    'intent' => $intent,
                    'user_id' => $userId,
                    'is_popup' => $isPopup,
                    'auth_check' => Auth::check(),
                ]);
            }

            $socialUser = Socialite::driver($provider)->user();

            // If linking, try to restore user from state if session was lost
            if ($intent === 'link') {
                $user = null;
                if (Auth::check()) {
                    $user = Auth::user();
                } elseif ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        // Log the user in for this request
                        Auth::login($user);
                        $user = Auth::user(); // Refresh to ensure we have the authenticated user
                    }
                }

                if (! $user) {
                    Log::warning('Social auth linking failed: User not found', [
                        'provider' => $provider,
                        'user_id' => $userId,
                        'is_popup' => $isPopup,
                        'auth_check' => Auth::check(),
                    ]);

                    if ($isPopup) {
                        return Inertia::render('auth/OAuthCallback', [
                            'success' => false,
                            'error' => __('auth.oauth.session_expired_link'),
                            'provider' => $provider,
                        ]);
                    }

                    return redirect()->route('login')
                        ->with('error', __('auth.oauth.session_expired_link'));
                }

                $result = $this->handleAccountLinking($user, $provider, $socialUser, $isPopup);
                if ($isPopup && $result instanceof Response) {
                    return $result;
                }

                return $result;
            }

            return $this->handleLoginOrRegister($provider, $socialUser, $isPopup);
        } catch (\Exception $e) {
            Log::error('Social auth error: '.$e->getMessage(), [
                'provider' => $provider,
                'exception' => $e,
            ]);

            $isPopup = false;
            $state = $request->get('state');
            if ($state) {
                $decoded = json_decode(base64_decode($state), true);
                $isPopup = $decoded['popup'] ?? false;
            }

            $errorMessage = __('auth.oauth.unable_to_authenticate', ['provider' => ucfirst($provider)]);

            if ($isPopup) {
                return Inertia::render('auth/OAuthCallback', [
                    'success' => false,
                    'error' => $errorMessage,
                    'provider' => $provider,
                ]);
            }

            return redirect()->route('login')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Link a social account to the authenticated user.
     */
    public function link(Request $request, string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to link a social account.');
        }

        // Always include popup flag if it's in the request
        $request->merge(['intent' => 'link']);

        return $this->redirect($request, $provider);
    }

    /**
     * Show the confirm-unlink page. Requires recent password confirmation (same as 2FA).
     * User must have a password set (enforced by ensure.user.has.password middleware).
     */
    public function showUnlinkConfirm(Request $request, string $provider): RedirectResponse|Response
    {
        $this->validateProvider($provider);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->getSocialAccount($provider)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Social account not found.');
        }

        return Inertia::render('settings/ConfirmUnlink', [
            'provider' => $provider,
        ]);
    }

    /**
     * Unlink a social account from the authenticated user.
     * Requires recent password confirmation (password.confirm middleware).
     */
    public function unlink(Request $request, string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user()->fresh() ?? $request->user();
        $socialAccount = $user->getSocialAccount($provider);

        if (! $socialAccount) {
            return redirect()->route('profile.edit')
                ->with('error', 'Social account not found.');
        }

        $hasPassword = ! empty($user->password);
        $otherSocialAccounts = $user->socialAccounts()->where('provider', '!=', $provider)->exists();

        if (! $hasPassword && ! $otherSocialAccounts) {
            Log::info('Social unlink blocked: no password', [
                'provider' => $provider,
                'user_id' => $user->id,
            ]);

            return redirect()->route('profile.edit')
                ->with('error', 'You cannot unlink your only authentication method. Please set a password in Password settings first.');
        }

        $socialAccount->delete();

        return redirect()->route('profile.edit')
            ->with('status', ucfirst($provider).' account unlinked successfully.');
    }

    /**
     * Handle login or registration flow.
     */
    private function handleLoginOrRegister(string $provider, $socialUser, bool $isPopup = false): RedirectResponse|Response
    {
        return DB::transaction(function () use ($provider, $socialUser, $isPopup) {
            // Check if social account already exists
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            Log::info('OAuth flow check', [
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'social_account_exists' => $socialAccount ? true : false,
                'email' => $socialUser->getEmail(),
            ]);

            if ($socialAccount) {
                $this->updateUserAvatarFromSocial($socialAccount->user, $socialUser);
                Auth::login($socialAccount->user);

                Log::info('Existing user logged in', [
                    'user_id' => $socialAccount->user->id,
                    'email' => $socialAccount->user->email,
                ]);

                if ($isPopup) {
                    return Inertia::render('auth/OAuthCallback', [
                        'success' => true,
                        'provider' => $provider,
                    ]);
                }

                return redirect()->route('dashboard');
            }

            // Check if user with same email exists
            $user = User::where('email', $socialUser->getEmail())->first();

            Log::info('User email check', [
                'email' => $socialUser->getEmail(),
                'user_exists' => $user ? true : false,
            ]);

            if ($user) {
                $this->updateUserAvatarFromSocial($user, $socialUser);
                $this->createSocialAccount($user, $provider, $socialUser);
                Auth::login($user);

                Log::info('Existing social account linked', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                if ($isPopup) {
                    return Inertia::render('auth/OAuthCallback', [
                        'success' => true,
                        'provider' => $provider,
                    ]);
                }

                return redirect()->route('dashboard')
                    ->with('status', ucfirst($provider).' account linked successfully.');
            }

            // Create new user
            Log::info('Creating new user', [
                'provider' => $provider,
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
            ]);

            $user = $this->createUserFromSocial($provider, $socialUser);

            Log::info('New user created', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $this->createSocialAccount($user, $provider, $socialUser);
            Auth::login($user);

            Log::info('User authenticated', [
                'user_id' => $user->id,
                'auth_check' => Auth::check(),
            ]);

            if ($isPopup) {
                return Inertia::render('auth/OAuthCallback', [
                    'success' => true,
                    'provider' => $provider,
                ]);
            }

            return redirect()->route('dashboard');
        });
    }

    /**
     * Handle account linking for authenticated user.
     */
    private function handleAccountLinking(User $user, string $provider, $socialUser, bool $isPopup = false): RedirectResponse|Response
    {
        return DB::transaction(function () use ($user, $provider, $socialUser, $isPopup) {
            // Check if social account is already linked to another user
            $existingAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($existingAccount) {
                if ($isPopup) {
                    return Inertia::render('auth/OAuthCallback', [
                        'success' => false,
                        'error' => 'This '.ucfirst($provider).' account is already linked to another account.',
                        'provider' => $provider,
                    ]);
                }

                return redirect()->route('profile.edit')
                    ->with('error', 'This '.ucfirst($provider).' account is already linked to another account.');
            }

            // Check if user already has this provider linked
            if ($user->hasSocialAccount($provider)) {
                if ($isPopup) {
                    return Inertia::render('auth/OAuthCallback', [
                        'success' => false,
                        'error' => 'You already have a '.ucfirst($provider).' account linked.',
                        'provider' => $provider,
                    ]);
                }

                return redirect()->route('profile.edit')
                    ->with('error', 'You already have a '.ucfirst($provider).' account linked.');
            }

            $this->updateUserAvatarFromSocial($user, $socialUser);
            $this->createSocialAccount($user, $provider, $socialUser);

            if ($isPopup) {
                return Inertia::render('auth/OAuthCallback', [
                    'success' => true,
                    'provider' => $provider,
                ]);
            }

            return redirect()->route('profile.edit')
                ->with('status', __('auth.oauth.account_linked_success', ['provider' => ucfirst($provider)]));
        });
    }

    /**
     * Get avatar URL from social user if available.
     */
    private function getAvatarFromSocialUser($socialUser): ?string
    {
        $url = $socialUser->getAvatar();

        return is_string($url) && $url !== '' ? $url : null;
    }

    /**
     * Update the user's avatar from the social provider when present.
     */
    private function updateUserAvatarFromSocial(User $user, $socialUser): void
    {
        $avatar = $this->getAvatarFromSocialUser($socialUser);
        if ($avatar !== null) {
            $user->update(['avatar' => $avatar]);
        }
    }

    /**
     * Create a user from social provider data.
     * Same email as OAuth; no password set so the user can add one later in Password settings if they want.
     */
    private function createUserFromSocial(string $provider, $socialUser): User
    {
        $email = $socialUser->getEmail();

        if (! $email) {
            throw new \Exception('Email is required but not provided by '.ucfirst($provider));
        }

        $avatar = $this->getAvatarFromSocialUser($socialUser);

        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $email,
            'avatar' => $avatar,
            'email_verified_at' => now(),
            'password' => null,
        ]);

        $user->assignRole(Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => config('auth.defaults.guard')]
        ));

        return $user;
    }

    /**
     * Create a social account record.
     */
    private function createSocialAccount(User $user, string $provider, $socialUser): SocialAccount
    {
        return SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]
        );
    }

    /**
     * Validate that the provider is supported.
     */
    private function validateProvider(string $provider): void
    {
        if (! SocialProvider::isValid($provider)) {
            abort(404, 'Unsupported authentication provider.');
        }
    }
}
