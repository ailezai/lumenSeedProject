@php($preAction = session()->pull('__preAction'))
@if(!empty($preAction) && !empty($preAction['method']) && !empty($preAction['message']) && !empty($preAction['type']))
    <script>
        $(document).ready(function() {
            $.admin.preAction('{{ $preAction['method'] }}', '{{ $preAction['message'] }}', '{{ $preAction['type'] }}');
        });
    </script>
@endif
