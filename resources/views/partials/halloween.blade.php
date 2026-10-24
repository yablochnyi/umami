<div class="halloween-decor" id="halloweenDecor" aria-hidden="true" inert>
    <img class="hw-web hw-web-left" src="/assets/umami/halloween/web.webp" width="480" height="450" alt="Halloween" decoding="async" fetchpriority="low">
    <img class="hw-web hw-web-right" src="/assets/umami/halloween/web.webp" width="480" height="450" alt="Halloween" decoding="async" fetchpriority="low">
    <div class="hw-hover hw-hover-one"><span class="hw-bat"><i class="hw-wing-left"></i><i class="hw-bat-body"></i><i class="hw-wing-right"></i></span></div>
    <div class="hw-hover hw-hover-two"><span class="hw-bat"><i class="hw-wing-left"></i><i class="hw-bat-body"></i><i class="hw-wing-right"></i></span></div>
    @for($leaf = 1; $leaf <= 16; $leaf++)
        <span class="hw-leaf hw-leaf-{{ $leaf }}"></span>
    @endfor
</div>
