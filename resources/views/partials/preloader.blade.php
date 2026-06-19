{{--
    Анимация загрузки сайта (прелоадер).

    Использование:
        @include('partials.preloader')                       — вариант по умолчанию (stamp)
        @include('partials.preloader', ['variant' => 'ring']) — выбрать другую анимацию

    Доступные варианты ($variant):
        stamp  — печать «ОЧЕРЕДЬ» впечатывается в лист (в стиле «бумаги»)
        sheets — стопка листов выезжает один за другим
        bars   — три строки документа «заполняются» слева направо
        ring   — вращающееся кольцо (классический спиннер)
        dots   — три прыгающих квадратных точки
        globe  — вращающийся монохромный глобус с контурами материков и вихрем
                 (чистый canvas, без внешних библиотек и сети; цвета берутся из темы)

    Поведение:
        Показывается один раз за сессию браузера (sessionStorage).
        Чтобы показывать при КАЖДОЙ загрузке — удалите блок про sessionStorage
        в двух местах ниже (помечено «ПОКАЗ ОДИН РАЗ ЗА СЕССИЮ»).
--}}
@php($variant = $variant ?? 'stamp')

<style>
    /* ---- Палитра прелоадера: повторяет переменные темы, чтобы не было вспышки ---- */
    .preloader {
        --pl-bg:#f0efeb; --pl-ink:#111111; --pl-card:#ffffff; --pl-line:#d9d7d2; --pl-shadow:#d9d7d2;
        --pl-display:"Montserrat","Arial Black",sans-serif;
    }
    @media (prefers-color-scheme: dark) {
        .preloader { --pl-bg:#191815; --pl-ink:#e9e7e2; --pl-card:#242320; --pl-line:#3a3833; --pl-shadow:#100f0d; }
    }
    [data-theme="dark"] .preloader { --pl-bg:#191815; --pl-ink:#e9e7e2; --pl-card:#242320; --pl-line:#3a3833; --pl-shadow:#100f0d; }
    [data-theme="light"] .preloader { --pl-bg:#f0efeb; --pl-ink:#111111; --pl-card:#ffffff; --pl-line:#d9d7d2; --pl-shadow:#d9d7d2; }

    /* ---- Оверлей на весь экран ---- */
    .preloader {
        position: fixed; inset: 0; z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        background: var(--pl-bg);
        opacity: 1; transition: opacity .45s ease;
    }
    .preloader.is-hidden { opacity: 0; pointer-events: none; }
    /* Если показ уже был в этой сессии — не мигаем вообще */
    .preloader-skip .preloader { display: none !important; }

    /* ============================================================
       Вариант: STAMP — печать впечатывается в лист
       ============================================================ */
    .preloader[data-anim="stamp"] .pl-stamp {
        font-family: var(--pl-display); font-weight: 700;
        text-transform: uppercase; letter-spacing: 2px; font-size: 22px;
        color: var(--pl-ink); padding: 16px 26px;
        border: 3px solid var(--pl-ink); border-radius: 4px;
        background: var(--pl-card); box-shadow: 5px 5px 0 var(--pl-shadow);
        animation: pl-stamp 1.1s var(--pl-ease, cubic-bezier(.23,1,.32,1)) infinite;
    }
    @keyframes pl-stamp {
        0%   { transform: scale(1.8) rotate(-8deg); opacity: 0; }
        25%  { transform: scale(1)   rotate(-3deg); opacity: 1; }
        35%  { transform: scale(.96) rotate(-3deg); }
        45%  { transform: scale(1)   rotate(-3deg); }
        80%  { transform: scale(1)   rotate(-3deg); opacity: 1; }
        100% { transform: scale(1)   rotate(-3deg); opacity: 0; }
    }

    /* ============================================================
       Вариант: SHEETS — листы выезжают стопкой
       ============================================================ */
    .preloader[data-anim="sheets"] .pl-sheets { position: relative; width: 70px; height: 90px; }
    .preloader[data-anim="sheets"] .pl-sheets span {
        position: absolute; inset: 0; border-radius: 3px;
        background: var(--pl-card); border: 2px solid var(--pl-ink);
        box-shadow: 4px 4px 0 var(--pl-shadow);
        animation: pl-sheets 1.2s ease-in-out infinite;
    }
    .preloader[data-anim="sheets"] .pl-sheets span:nth-child(1) { animation-delay: 0s; }
    .preloader[data-anim="sheets"] .pl-sheets span:nth-child(2) { animation-delay: .2s; }
    .preloader[data-anim="sheets"] .pl-sheets span:nth-child(3) { animation-delay: .4s; }
    @keyframes pl-sheets {
        0%   { transform: translate(0,0) rotate(0); opacity: 0; }
        30%  { transform: translate(0,0) rotate(0); opacity: 1; }
        70%  { transform: translate(14px,-10px) rotate(6deg); opacity: 1; }
        100% { transform: translate(28px,-20px) rotate(10deg); opacity: 0; }
    }

    /* ============================================================
       Вариант: BARS — строки документа заполняются
       ============================================================ */
    .preloader[data-anim="bars"] .pl-bars {
        width: 180px; padding: 18px; border-radius: 3px;
        background: var(--pl-card); border: 2px solid var(--pl-line);
        box-shadow: 4px 4px 0 var(--pl-shadow);
        display: flex; flex-direction: column; gap: 10px;
    }
    .preloader[data-anim="bars"] .pl-bars i {
        display: block; height: 8px; border-radius: 2px;
        background: linear-gradient(90deg, var(--pl-ink) 50%, var(--pl-line) 50%);
        background-size: 200% 100%; background-position: 100% 0;
        animation: pl-bars 1.4s ease-in-out infinite;
    }
    .preloader[data-anim="bars"] .pl-bars i:nth-child(1) { width: 100%; animation-delay: 0s; }
    .preloader[data-anim="bars"] .pl-bars i:nth-child(2) { width: 85%;  animation-delay: .15s; }
    .preloader[data-anim="bars"] .pl-bars i:nth-child(3) { width: 60%;  animation-delay: .3s; }
    @keyframes pl-bars {
        0%   { background-position: 100% 0; }
        60%  { background-position: 0 0; }
        100% { background-position: 0 0; }
    }

    /* ============================================================
       Вариант: RING — вращающееся кольцо
       ============================================================ */
    .preloader[data-anim="ring"] .pl-ring {
        width: 54px; height: 54px; border-radius: 50%;
        border: 5px solid var(--pl-line);
        border-top-color: var(--pl-ink);
        animation: pl-ring .8s linear infinite;
    }
    @keyframes pl-ring { to { transform: rotate(360deg); } }

    /* ============================================================
       Вариант: DOTS — три прыгающих квадрата
       ============================================================ */
    .preloader[data-anim="dots"] .pl-dots { display: flex; gap: 10px; }
    .preloader[data-anim="dots"] .pl-dots i {
        width: 14px; height: 14px; border-radius: 2px;
        background: var(--pl-ink);
        animation: pl-dots .7s ease-in-out infinite;
    }
    .preloader[data-anim="dots"] .pl-dots i:nth-child(2) { animation-delay: .12s; }
    .preloader[data-anim="dots"] .pl-dots i:nth-child(3) { animation-delay: .24s; }
    @keyframes pl-dots {
        0%, 100% { transform: translateY(0); opacity: .5; }
        50%      { transform: translateY(-14px); opacity: 1; }
    }

    /* ============================================================
       Вариант: GLOBE — вращающийся глобус + вихрь (canvas, без библиотек)
       ============================================================ */
    .preloader[data-anim="globe"] .pl-globe { position: relative; width: 200px; height: 200px; }
    .preloader[data-anim="globe"] .pl-globe canvas {
        position: absolute; inset: 0; width: 200px; height: 200px;
    }
    /* Объёмное затенение сферы (блик сверху-слева, тень снизу-справа) */
    .preloader[data-anim="globe"] .pl-globe .pl-shade {
        position: absolute; inset: 26px; border-radius: 50%; pointer-events: none; z-index: 3;
        background:
            radial-gradient(circle at 34% 30%, rgba(255,255,255,.28), rgba(255,255,255,0) 55%),
            radial-gradient(circle at 70% 78%, rgba(0,0,0,.22), rgba(0,0,0,0) 60%);
    }

    /* ---- Уважаем «уменьшить движение» ---- */
    @media (prefers-reduced-motion: reduce) {
        .preloader *,
        .preloader [data-anim] * { animation: none !important; }
    }
</style>

{{-- ПОКАЗ ОДИН РАЗ ЗА СЕССИЮ: прячем оверлей мгновенно (до отрисовки), если уже показывали --}}
<script>
    if (sessionStorage.getItem('preloaderDone')) {
        document.documentElement.classList.add('preloader-skip');
    }
</script>

<div class="preloader" data-anim="{{ $variant }}" role="status" aria-label="Загрузка" aria-live="polite">
    @switch($variant)
        @case('sheets')
            <div class="pl-sheets"><span></span><span></span><span></span></div>
            @break
        @case('bars')
            <div class="pl-bars"><i></i><i></i><i></i></div>
            @break
        @case('ring')
            <div class="pl-ring"></div>
            @break
        @case('dots')
            <div class="pl-dots"><i></i><i></i><i></i></div>
            @break
        @case('globe')
            <div class="pl-globe">
                <canvas class="pl-whirl" width="400" height="400"></canvas>
                <canvas class="pl-sphere" width="400" height="400"></canvas>
                <div class="pl-shade"></div>
            </div>
            @break
        @default
            <div class="pl-stamp">Очередь</div>
    @endswitch
</div>

@if ($variant === 'globe')
<script>
    /* GLOBE — вращающийся глобус с контурами материков и вихрем.
       Чистый canvas: без D3/topojson и без обращений к сети. */
    (function () {
        var root = document.querySelector('.preloader[data-anim="globe"] .pl-globe');
        if (!root) return;
        var sphere = root.querySelector('.pl-sphere');
        var whirl  = root.querySelector('.pl-whirl');
        if (!sphere || !whirl) return;

        var gx = sphere.getContext('2d'); gx.scale(2, 2);
        var wx = whirl.getContext('2d');  wx.scale(2, 2);

        var S = 200, cx = S / 2, cy = S / 2, R = 74;
        var MW = 296, MH = 148, top = cy - MH / 2;     // полоска-карта 2:1, по высоте = диаметр шара

        // ---- цвета из темы (--text / --bg), чтобы совпадать со светлой/тёмной ----
        function rgbOf(varName, fallback) {
            var v = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
            var m = v.match(/^#([0-9a-f]{6})$/i);
            if (!m) return fallback;
            var n = parseInt(m[1], 16);
            return (n >> 16 & 255) + ',' + (n >> 8 & 255) + ',' + (n & 255);
        }
        var INK = rgbOf('--text', '43,41,38');
        var BG  = rgbOf('--bg', '244,242,238');

        // ---- контуры материков (lon,lat в градусах) — лёгкие силуэты ----
        var SHAPES = [
            [-168,65,-160,71,-130,70,-95,69,-80,62,-78,43,-66,45,-72,28,-97,16,-105,20,-115,30,-125,40,-125,48,-135,58,-150,59],
            [-78,8,-70,11,-60,5,-50,0,-43,-8,-40,-20,-48,-30,-55,-35,-58,-40,-65,-45,-72,-52,-70,-38,-72,-25,-78,-10,-80,0],
            [-16,15,-10,25,0,32,10,34,20,32,32,31,35,22,43,12,51,12,42,0,40,-12,35,-22,25,-34,18,-35,12,-18,8,4,-8,5],
            [-10,38,-5,43,0,48,2,51,-2,58,5,60,10,63,28,70,40,66,40,55,30,46,28,41,20,40,10,38],
            [40,66,60,72,100,76,140,73,160,68,180,66,170,60,140,55,145,45,135,35,122,30,120,22,108,18,100,8,95,18,88,22,75,22,68,24,60,25,57,40,48,42,40,48],
            [114,-22,122,-18,132,-12,142,-12,150,-22,153,-28,148,-38,138,-35,128,-32,118,-34],
            [-45,60,-30,60,-20,70,-25,80,-45,82,-58,76,-55,68],          // Гренландия
            [44,-16,50,-15,50,-25,46,-25],                               // Мадагаскар
            [130,31,140,35,142,40,140,43,133,35],                        // Япония
            [-6,50,-2,52,-3,58,-7,57],                                   // Британия
            [167,-44,174,-41,178,-37,173,-42]                            // Новая Зеландия
        ];

        // ---- собираем полоску-карту один раз ----
        var strip = document.createElement('canvas');
        strip.width = MW * 2; strip.height = MH * 2;
        var sp = strip.getContext('2d'); sp.scale(2, 2);
        var PX = function (lon) { return (lon + 180) / 360 * MW; };
        var PY = function (lat) { return (90 - lat) / 180 * MH; };

        function buildStrip() {
            sp.clearRect(0, 0, MW, MH);
            // сетка координат
            sp.strokeStyle = 'rgba(' + INK + ',0.16)'; sp.lineWidth = 0.5;
            for (var lon = -150; lon <= 150; lon += 30) { sp.beginPath(); sp.moveTo(PX(lon), 0); sp.lineTo(PX(lon), MH); sp.stroke(); }
            for (var lat = -60; lat <= 60; lat += 30) { sp.beginPath(); sp.moveTo(0, PY(lat)); sp.lineTo(MW, PY(lat)); sp.stroke(); }
            // материки
            for (var s = 0; s < SHAPES.length; s++) {
                var pts = SHAPES[s];
                sp.beginPath();
                for (var i = 0; i < pts.length; i += 2) {
                    var x = PX(pts[i]), y = PY(pts[i + 1]);
                    if (i === 0) sp.moveTo(x, y); else sp.lineTo(x, y);
                }
                sp.closePath();
                sp.fillStyle = 'rgba(' + INK + ',0.82)'; sp.fill();
                sp.strokeStyle = 'rgba(' + INK + ',1)'; sp.lineWidth = 0.5; sp.stroke();
            }
        }
        buildStrip();

        // ---- вихрь: дуги + искры ----
        var rnd = function () { return Math.random(); };
        var arcs = [], specks = [], i;
        for (i = 0; i < 7; i++) arcs.push({ radius: R + 12 + i * 5.5, len: 0.5 + rnd() * 1.1, start: rnd() * Math.PI * 2, speed: (0.6 + rnd() * 0.9) * (i % 2 ? -1 : 1), width: 2.4 - i * 0.18, alpha: 0.32 - i * 0.03 });
        for (i = 0; i < 14; i++) specks.push({ radius: R + 8 + rnd() * 34, ang: rnd() * Math.PI * 2, speed: (0.4 + rnd() * 1.2) * (rnd() < 0.5 ? -1 : 1), size: 0.7 + rnd() * 1.4, alpha: 0.15 + rnd() * 0.3 });

        function drawWhirl(t) {
            wx.clearRect(0, 0, S, S);
            wx.save(); wx.translate(cx, cy); wx.scale(1, 0.62); wx.rotate(-0.5);
            for (var a, k = 0; k < arcs.length; k++) { a = arcs[k]; var st = a.start + t * a.speed; wx.beginPath(); wx.arc(0, 0, a.radius, st, st + a.len); wx.lineWidth = a.width; wx.lineCap = 'round'; wx.strokeStyle = 'rgba(' + INK + ',' + a.alpha + ')'; wx.stroke(); }
            for (var p, j = 0; j < specks.length; j++) { p = specks[j]; var an = p.ang + t * p.speed; wx.beginPath(); wx.arc(Math.cos(an) * p.radius, Math.sin(an) * p.radius, p.size, 0, Math.PI * 2); wx.fillStyle = 'rgba(' + INK + ',' + p.alpha + ')'; wx.fill(); }
            wx.restore();
        }

        var panX = 0;
        function drawGlobe() {
            gx.clearRect(0, 0, S, S);
            gx.save();
            gx.beginPath(); gx.arc(cx, cy, R, 0, Math.PI * 2); gx.clip();
            gx.fillStyle = 'rgba(' + INK + ',0.04)'; gx.fillRect(0, 0, S, S);
            var off = panX % MW; if (off > 0) off -= MW;
            for (var i = -1; i <= 1; i++) gx.drawImage(strip, off + i * MW, top, MW, MH);
            gx.restore();
            gx.beginPath(); gx.arc(cx, cy, R, 0, Math.PI * 2); gx.lineWidth = 1.1; gx.strokeStyle = 'rgb(' + INK + ')'; gx.stroke();
        }

        // ---- запуск (с уважением к prefers-reduced-motion) ----
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce) { drawGlobe(); drawWhirl(0); return; }

        var last = performance.now();
        function frame(now) {
            if (!document.body.contains(sphere)) return;   // прелоадер убран — останавливаемся
            var dt = (now - last) / 1000; last = now;
            panX -= dt * (MW / 12);                         // горизонтальный сдвиг = «вращение»
            drawGlobe(); drawWhirl(now / 1000);
            requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    })();
</script>
@endif

<script>
    (function () {
        var el = document.querySelector('.preloader');
        if (!el) return;

        // Уже показывали в этой сессии — сразу убираем из DOM.
        if (document.documentElement.classList.contains('preloader-skip')) {
            el.remove();
            return;
        }

        var shownAt = Date.now();
        // для глобуса показываем чуть дольше, чтобы анимацию было видно
        var MIN_VISIBLE = el.dataset.anim === 'globe' ? 1100 : 600;

        function hide() {
            var wait = Math.max(0, MIN_VISIBLE - (Date.now() - shownAt));
            setTimeout(function () {
                el.classList.add('is-hidden');
                // ПОКАЗ ОДИН РАЗ ЗА СЕССИЮ: запоминаем, что уже показали
                try { sessionStorage.setItem('preloaderDone', '1'); } catch (e) {}
                el.addEventListener('transitionend', function () { el.remove(); }, { once: true });
                setTimeout(function () { if (el.parentNode) el.remove(); }, 700); // запасной таймер
            }, wait);
        }

        if (document.readyState === 'complete') hide();
        else window.addEventListener('load', hide);
    })();
</script>
