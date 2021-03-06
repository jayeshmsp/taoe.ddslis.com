@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level == 'error')
# Whoops!
@else
@if (\Session::has('reset_first_name'))
# Hello {{\Session::get('reset_first_name','')}}
@else
# Hello!
@endif
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
            $color = 'green';
            break;
        case 'error':
            $color = 'red';
            break;
        default:
            $color = 'blue';
    }
?>
@if (\Session::has('reset_user_id'))
@component('mail::button', ['url' => $actionUrl.'?q='.encrypt(\Session::get('reset_user_id')), 'color' => $color])
@else
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
@endif
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

<!-- Salutation -->
@if (! empty($salutation))
{{ $salutation }}
@else
Regards,<br>{{ config('app.name') }}
@endif

<?php 
    if (\Session::has('reset_user_id')){
        $actionUrl = $actionUrl.'?q='.encrypt(\Session::get('reset_user_id'));
    }
?>

<!-- Subcopy -->
@isset($actionText)
@component('mail::subcopy')
If you’re having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below
into your web browser: [{{ $actionUrl }}]({{ $actionUrl }})
@endcomponent
@endisset
@endcomponent
