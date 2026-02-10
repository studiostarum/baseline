<x-mail::message>
{{ __('website.contact.email_intro') }}

**{{ __('fields.name') }}:** {{ $data['name'] }}

**{{ __('fields.email') }}:** {{ $data['email'] }}

**{{ __('fields.message') }}:**  
{{ $data['message'] }}
</x-mail::message>
