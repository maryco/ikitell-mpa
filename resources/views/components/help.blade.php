<svg role="img" class="icon-help badge-{{ $type ?? 'info' }}" aria-hidden="true"
     @click="showInstMessage('{{ $slot }}', {{ (isset($type) && $type === 'alert') ? 2 : 1 }})">
    <use xlink:href="#help"></use>
    <title>Help</title>
</svg>
