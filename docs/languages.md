# Languages (i18n)

Baseline ships with locale switching and JSON translation files.

## Config

[config/locales.php](config/locales.php):

| Key | Description |
|-----|-------------|
| `available` | Array of locale codes (e.g. `['en', 'nl']`) |
| `labels` | Map of code → display name (e.g. `'en' => 'English'`) |
| `cookie_minutes` | How long the locale cookie lasts (default from `LOCALE_COOKIE_MINUTES`) |

## Translation files

Translations live in `lang/en.json`, `lang/nl.json`, etc. Use the `t('key')` helper (or the `useTranslations` composable in Vue) to output translated strings. Add keys to the JSON files for your app.

## Adding a locale

1. Add the code to `config/locales.available` and `config/locales.labels`.
2. Create `lang/xx.json` (copy from `lang/en.json` and translate).
3. If the locale switch route is restricted by a `where` pattern (e.g. `en|nl`), update it in [routes/web.php](routes/web.php) to include the new code so `/locale/xx` is accepted.
