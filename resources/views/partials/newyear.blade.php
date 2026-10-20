<div class="newyear-decor" id="newyearDecor" aria-hidden="true" inert>
    <img class="ny-evergreen ny-left" src="/assets/umami/newyear/evergreen.webp" width="640" height="585" alt="" decoding="async" fetchpriority="low">
    <img class="ny-evergreen ny-right" src="/assets/umami/newyear/evergreen.webp" width="640" height="585" alt="" decoding="async" fetchpriority="low">
    <div class="ny-lights">
        @for($light = 0; $light < 64; $light++)
            <i class="ny-bulb"></i>
        @endfor
    </div>
    <img class="ny-snowman" src="/assets/umami/newyear/snowman.webp" width="240" height="360" alt="" decoding="async" fetchpriority="low">
    <div class="ny-story">
        <div class="ny-snack">
            <span class="ny-plate"></span>
            <img class="ny-sushi" src="/assets/umami/newyear/sushi.webp" width="160" height="107" alt="" decoding="async">
        </div>
        <div class="ny-fox">
            <div class="ny-fox-run">
                @foreach (['16 160 600 400', '620 160 490 400', '1114 160 563 400', '1680 160 476 400'] as $frame)
                    <svg class="ny-fox-frame ny-frame-{{ $loop->index }}" viewBox="{{ $frame }}" focusable="false" aria-hidden="true">
                        <image href="/assets/umami/newyear/fox-run.webp" width="2172" height="724"/>
                    </svg>
                @endforeach
            </div>
            <div class="ny-fox-eat">
                <img src="/assets/umami/newyear/fox-eat.webp" width="384" height="256" alt="" decoding="async">
            </div>
        </div>
    </div>
</div>
