<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Portfolio | {{ $slug }}</title>
        <meta name="description" content="Scroll exploration portfolio shell for {{ $slug }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-black antialiased">
        <style>
            html {
                scroll-behavior: smooth;
            }

            body {
                margin: 0;
                background: #090909;
                overflow-x: hidden;
            }

            .portfolio-shell {
                position: relative;
                z-index: 1;
            }

            .portfolio-shell::before {
                content: '';
                position: fixed;
                inset: 0;
                z-index: 0;
                pointer-events: none;
                background:
                    radial-gradient(circle at top, rgba(255, 255, 255, 0.1), transparent 38%),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0));
                opacity: 0.9;
            }

            .portfolio-section {
                position: relative;
                min-height: 100vh;
                min-height: 100svh;
                scroll-snap-align: start;
                scroll-snap-stop: always;
            }

            .portfolio-stage {
                position: relative;
                z-index: 1;
                min-height: 100vh;
                min-height: 100svh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                overflow: hidden;
            }

            .portfolio-motion-layer {
                position: absolute;
                inset: 0;
                overflow: hidden;
                pointer-events: none;
            }

            .portfolio-motion-grid {
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(color-mix(in srgb, var(--page-fg, #ffffff) 14%, transparent) 1px, transparent 1px),
                    linear-gradient(90deg, color-mix(in srgb, var(--page-fg, #ffffff) 14%, transparent) 1px, transparent 1px);
                background-size: 4.5rem 4.5rem;
                opacity: 0.22;
                transform: translate3d(var(--grid-x, 0px), var(--grid-y, 0px), 0);
                will-change: transform;
            }

            .portfolio-motion-blob {
                position: absolute;
                border-radius: 9999px;
                opacity: 0.22;
                filter: blur(8px);
                transform: translate3d(var(--blob-x, 0px), var(--blob-y, 0px), 0) scale(var(--blob-scale, 1));
                will-change: transform;
            }

            .portfolio-motion-ring {
                position: absolute;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 24%, transparent);
                opacity: 0.3;
                transform: translate3d(var(--ring-x, 0px), var(--ring-y, 0px), 0) scale(var(--ring-scale, 1));
                will-change: transform;
            }

            .portfolio-motion-bar {
                position: absolute;
                height: 1px;
                background: color-mix(in srgb, var(--page-fg, #ffffff) 32%, transparent);
                opacity: 0.48;
                transform: translate3d(var(--bar-x, 0px), var(--bar-y, 0px), 0) scaleX(var(--bar-scale, 1));
                transform-origin: left center;
                will-change: transform;
            }

            .portfolio-motion-panel {
                position: absolute;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 14%, transparent);
                background:
                    linear-gradient(180deg, color-mix(in srgb, var(--page-fg, #ffffff) 8%, transparent), color-mix(in srgb, var(--page-fg, #ffffff) 2%, transparent));
                opacity: 0.26;
                transform: translate3d(var(--panel-x, 0px), var(--panel-y, 0px), 0) rotate(var(--panel-rotate, 0deg));
                will-change: transform;
            }

            .portfolio-motion-orbiter {
                position: absolute;
                width: 14rem;
                height: 14rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 24%, transparent);
                opacity: 0.34;
            }

            .portfolio-motion-orbiter::after {
                content: '';
                position: absolute;
                left: 50%;
                top: 50%;
                width: 0.75rem;
                height: 0.75rem;
                margin-left: -0.375rem;
                margin-top: -0.375rem;
                border-radius: 9999px;
                background: color-mix(in srgb, var(--page-fg, #ffffff) 85%, transparent);
                transform:
                    rotate(var(--orbit-angle, 0deg))
                    translateX(calc(var(--orbit-radius, 5.75rem)))
                    rotate(calc(var(--orbit-angle, 0deg) * -1));
                transform-origin: center;
            }

            .portfolio-motion-wave {
                position: absolute;
                left: -10%;
                width: 120%;
                height: 16rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 22%, transparent);
                opacity: 0.32;
                transform: translate3d(var(--wave-x, 0px), var(--wave-y, 0px), 0) scaleY(var(--wave-scale, 1));
                will-change: transform;
            }

            .portfolio-frame {
                position: relative;
                z-index: 1;
                width: min(100%, 1100px);
                color: var(--page-fg, rgba(255, 255, 255, 0.92));
                opacity: 0.28;
                transform: translate3d(0, 3.5rem, 0) scale(0.965);
                filter: blur(12px);
                transition:
                    opacity 800ms cubic-bezier(0.22, 1, 0.36, 1),
                    transform 950ms cubic-bezier(0.22, 1, 0.36, 1),
                    filter 950ms cubic-bezier(0.22, 1, 0.36, 1);
            }

            .portfolio-section.is-active .portfolio-frame {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
                filter: blur(0);
            }

            .portfolio-kicker {
                margin: 0;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.3em;
                text-transform: uppercase;
            }

            .portfolio-title {
                margin: 0.75rem 0 0;
                font-size: clamp(3rem, 8vw, 7rem);
                line-height: 0.92;
                font-weight: 800;
                letter-spacing: -0.05em;
                text-transform: uppercase;
            }

            .portfolio-copy {
                margin: 1.25rem 0 0;
                max-width: 34rem;
                font-size: clamp(1rem, 1.6vw, 1.25rem);
                line-height: 1.7;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 76%, transparent);
            }

            .portfolio-grid {
                display: grid;
                grid-template-columns: repeat(12, minmax(0, 1fr));
                gap: 1rem;
                margin-top: 2rem;
            }

            .portfolio-card {
                min-height: 7rem;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 22%, transparent);
                background: color-mix(in srgb, var(--page-fg, #ffffff) 10%, transparent);
                backdrop-filter: blur(8px);
                box-shadow: 0 24px 60px rgba(0, 0, 0, 0.16);
                transition:
                    transform 700ms cubic-bezier(0.22, 1, 0.36, 1),
                    opacity 700ms cubic-bezier(0.22, 1, 0.36, 1),
                    border-color 700ms cubic-bezier(0.22, 1, 0.36, 1);
                opacity: 0.45;
                transform: translate3d(0, 1.25rem, 0);
            }

            .portfolio-section.is-active .portfolio-card {
                opacity: 1;
                transform: translate3d(0, 0, 0);
                border-color: color-mix(in srgb, var(--page-fg, #ffffff) 30%, transparent);
            }

            .portfolio-card-tall {
                min-height: 18rem;
            }

            .portfolio-card-wide {
                min-height: 12rem;
            }

            .portfolio-card-label {
                margin: 0;
                padding: 1rem;
                font-size: 0.78rem;
                font-weight: 600;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 88%, transparent);
            }

            .portfolio-card-copy {
                margin: 0;
                padding: 0 1rem 1rem;
                font-size: 0.98rem;
                line-height: 1.7;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 76%, transparent);
            }

            .portfolio-card-meta {
                margin: 0;
                padding: 0 1rem 0.5rem;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 58%, transparent);
            }

            .portfolio-badge-row {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                padding: 0 1rem 1rem;
            }

            .portfolio-badge {
                display: inline-flex;
                align-items: center;
                position: relative;
                min-height: 2rem;
                padding: 0.38rem 0.95rem 0.38rem 0.78rem;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 24%, transparent);
                background: color-mix(in srgb, var(--page-fg, #ffffff) 16%, transparent);
                clip-path: polygon(0 0, calc(100% - 0.8rem) 0, 100% 50%, calc(100% - 0.8rem) 100%, 0 100%, 0.55rem 50%);
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 94%, transparent);
                box-shadow:
                    inset 0 1px 0 color-mix(in srgb, var(--page-fg, #ffffff) 12%, transparent),
                    0 0.4rem 1rem rgba(0, 0, 0, 0.08);
            }

            .portfolio-badge::before {
                content: '';
                width: 0.3rem;
                height: 0.3rem;
                margin-right: 0.5rem;
                border-radius: 9999px;
                background: currentColor;
                opacity: 0.72;
            }

            .portfolio-link-list {
                display: grid;
                gap: 0.85rem;
                margin-top: 2rem;
                max-width: 32rem;
            }

            .portfolio-link-item {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 1rem;
                padding-bottom: 0.65rem;
                border-bottom: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 16%, transparent);
                color: inherit;
                text-decoration: none;
            }

            .portfolio-link-label {
                margin: 0;
                font-size: 0.76rem;
                font-weight: 700;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 62%, transparent);
            }

            .portfolio-link-value {
                margin: 0;
                font-size: clamp(1rem, 1.5vw, 1.2rem);
                line-height: 1.4;
                text-align: right;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 92%, transparent);
            }

            .portfolio-marquee {
                position: relative;
                margin-top: 2rem;
                overflow: hidden;
                mask-image: linear-gradient(90deg, transparent 0%, #000 8%, #000 92%, transparent 100%);
                -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 8%, #000 92%, transparent 100%);
            }

            .portfolio-marquee-track {
                display: flex;
                width: max-content;
                gap: 1rem;
                animation: portfolio-marquee 28s linear infinite;
            }

            .portfolio-marquee:hover .portfolio-marquee-track {
                animation-play-state: paused;
            }

            .portfolio-marquee .portfolio-card {
                width: min(18rem, 72vw);
                min-height: 12rem;
                flex: 0 0 auto;
                opacity: 1;
                transform: none;
            }

            @keyframes portfolio-marquee {
                from {
                    transform: translateX(0);
                }

                to {
                    transform: translateX(calc(-50% - 0.5rem));
                }
            }

            .portfolio-current-layout {
                position: relative;
                min-height: 34rem;
            }

            .portfolio-current-meta {
                position: relative;
                z-index: 2;
                max-width: 42rem;
                padding: 0;
            }

            .portfolio-current-label {
                margin: 0;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.3em;
                text-transform: uppercase;
            }

            .portfolio-current-role {
                margin: 0.75rem 0 0;
                font-size: clamp(3rem, 8vw, 7rem);
                line-height: 0.92;
                font-weight: 800;
                letter-spacing: -0.05em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .portfolio-current-company,
            .portfolio-current-date {
                margin: 0;
                max-width: 34rem;
                font-size: clamp(1rem, 1.6vw, 1.25rem);
                line-height: 1.7;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 76%, transparent);
            }

            .portfolio-current-date {
                margin-top: 0.9rem;
                font-size: 0.78rem;
                line-height: 1.4;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 58%, transparent);
            }

            .portfolio-current-carousel {
                position: absolute;
                right: 0;
                bottom: 0;
                width: min(68%, 58rem);
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                z-index: 1;
            }

            .portfolio-carousel {
                position: relative;
                overflow: hidden;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 16%, transparent);
                background: color-mix(in srgb, var(--page-fg, #ffffff) 7%, transparent);
                backdrop-filter: blur(10px);
                width: min(100%, 54rem);
                margin-top: 5.5rem;
            }

            .portfolio-carousel-track {
                display: flex;
                transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
            }

            .portfolio-carousel-slide {
                flex: 0 0 100%;
                min-width: 100%;
                padding: 1.1rem;
            }

            .portfolio-site-card {
                position: relative;
                min-height: 21rem;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 12%, transparent);
                background:
                    linear-gradient(180deg, color-mix(in srgb, var(--page-fg, #ffffff) 10%, transparent), color-mix(in srgb, var(--page-fg, #ffffff) 4%, transparent)),
                    linear-gradient(135deg, rgba(255, 255, 255, 0.05), transparent 55%);
                overflow: hidden;
            }

            .portfolio-site-media {
                position: absolute;
                inset: 0;
                overflow: hidden;
            }

            .portfolio-site-image {
                width: 100%;
                height: auto;
                display: block;
                transform: translateY(0);
                animation: none;
                object-fit: cover;
                object-position: top center;
                will-change: transform;
            }

            .portfolio-site-media.has-pan .portfolio-site-image {
                animation: portfolio-site-pan var(--pan-duration, 16s) linear infinite alternate;
            }

            @keyframes portfolio-site-pan {
                from {
                    transform: translateY(0);
                }

                to {
                    transform: translateY(calc(-1 * var(--pan-distance, 0px)));
                }
            }

            .portfolio-site-copy {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1;
                padding: 7rem 1.6rem 3.1rem;
                background: linear-gradient(180deg, rgba(12, 12, 12, 0) 0%, rgba(12, 12, 12, 0.16) 42%, rgba(12, 12, 12, 0.52) 100%);
            }

            .portfolio-site-index {
                margin: 0;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 62%, transparent);
            }

            .portfolio-site-title {
                margin: 1.1rem 0 0;
                font-size: clamp(1.45rem, 2vw, 2rem);
                line-height: 1;
                font-weight: 800;
                letter-spacing: -0.04em;
            }

            .portfolio-site-domain {
                margin: 0.45rem 0 0;
                font-size: 0.88rem;
                line-height: 1.5;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 68%, transparent);
            }

            .portfolio-site-link {
                position: absolute;
                right: 1.6rem;
                bottom: 1.15rem;
                z-index: 2;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.84rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: inherit;
            }

            .portfolio-carousel-footer {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.9rem;
                margin-top: 1rem;
                position: relative;
                z-index: 6;
                pointer-events: auto;
            }

            .portfolio-carousel-nav {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .portfolio-carousel-button {
                width: 2rem;
                height: 2rem;
                border: 1px solid color-mix(in srgb, var(--page-fg, #ffffff) 18%, transparent);
                background: color-mix(in srgb, var(--page-fg, #ffffff) 7%, transparent);
                color: inherit;
                font-size: 0.92rem;
                line-height: 1;
            }

            .portfolio-carousel-count {
                margin: 0;
                min-width: 3.2rem;
                text-align: center;
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--page-fg, #ffffff) 62%, transparent);
            }

            .portfolio-footer-note {
                position: fixed;
                left: 50%;
                bottom: 1.15rem;
                transform: translateX(-50%);
                z-index: 20;
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: rgba(255, 255, 255, 0.72);
                transition: opacity 300ms ease;
            }

            .portfolio-contact-section {
                --section-lift: 0px;
            }

            .portfolio-contact-section .portfolio-stage {
                transform: translate3d(0, calc(var(--section-lift, 0px) * -1), 0);
                will-change: transform;
            }

            .portfolio-reveal-footer {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 0;
                width: 100%;
                min-height: 6rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.9rem;
                padding: 1.15rem 1.25rem 1.4rem;
                background: #000000;
                overflow: hidden;
            }

            .portfolio-reveal-footer-text {
                margin: 0;
                font-size: 0.58rem;
                font-weight: 500;
                letter-spacing: 0.08em;
                text-transform: lowercase;
                color: rgba(255, 255, 255, 0.74);
                white-space: nowrap;
            }

            .portfolio-reveal-footer-sep {
                width: 0.24rem;
                height: 0.24rem;
                border-radius: 9999px;
                background: rgba(255, 255, 255, 0.38);
                flex: 0 0 auto;
            }

            .portfolio-palette-tag {
                position: fixed;
                right: 1.5rem;
                bottom: 1.2rem;
                z-index: 20;
                min-width: 11.5rem;
                border: 1px solid rgba(255, 255, 255, 0.14);
                background: rgba(10, 10, 10, 0.22);
                padding: 0.65rem 0.75rem;
                backdrop-filter: blur(14px);
                color: rgba(255, 255, 255, 0.94);
                transition:
                    border-color 300ms ease,
                    color 300ms ease,
                    background-color 300ms ease;
            }

            .portfolio-palette-grid {
                display: grid;
                gap: 0.45rem;
            }

            .portfolio-palette-item {
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 0.35rem 0.55rem;
                align-items: center;
                border: 1px solid color-mix(in srgb, currentColor 10%, transparent);
                background: color-mix(in srgb, currentColor 5%, transparent);
                padding: 0.42rem 0.5rem;
            }

            .portfolio-palette-swatch {
                width: 0.55rem;
                height: 0.55rem;
                border-radius: 9999px;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .portfolio-palette-label,
            .portfolio-palette-code {
                margin: 0;
                font-size: 0.68rem;
                line-height: 1.2;
            }

            .portfolio-palette-label {
                letter-spacing: 0.08em;
                text-transform: uppercase;
                opacity: 0.72;
            }

            .portfolio-palette-code {
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .portfolio-progress {
                position: fixed;
                left: 1.5rem;
                top: 50%;
                z-index: 20;
                display: flex;
                flex-direction: column;
                gap: 0.7rem;
                transform: translateY(-50%);
            }

            .portfolio-progress-dot {
                width: 0.6rem;
                height: 0.6rem;
                border-radius: 9999px;
                border: 1px solid rgba(255, 255, 255, 0.32);
                background: transparent;
                opacity: 0.45;
                transition:
                    transform 320ms cubic-bezier(0.22, 1, 0.36, 1),
                    background-color 320ms cubic-bezier(0.22, 1, 0.36, 1),
                    opacity 320ms cubic-bezier(0.22, 1, 0.36, 1);
            }

            .portfolio-progress-dot.is-active {
                background: rgba(255, 255, 255, 0.92);
                opacity: 1;
                transform: scale(1.25);
            }

            @media (prefers-reduced-motion: reduce) {
                html {
                    scroll-behavior: auto;
                }

                .portfolio-frame,
                .portfolio-card,
                .portfolio-progress-dot,
                .portfolio-footer-note {
                    transition: none;
                }
            }

            @media (max-width: 768px) {
                .portfolio-stage {
                    padding: 1.5rem;
                }

                .portfolio-current-layout {
                    min-height: 34rem;
                }

                .portfolio-current-meta {
                    max-width: 100%;
                }

                .portfolio-current-role {
                    white-space: normal;
                }

                .portfolio-current-carousel {
                    left: auto;
                    right: 0;
                    bottom: -0.25rem;
                    width: min(92%, 25rem);
                    display: flex;
                    align-items: flex-end;
                    z-index: 1;
                }

                .portfolio-carousel {
                    width: 100%;
                    margin-top: 0;
                }

                .portfolio-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .portfolio-marquee {
                    mask-image: none;
                    -webkit-mask-image: none;
                }

                .portfolio-marquee-track {
                    gap: 0.75rem;
                    animation-duration: 24s;
                }

                .portfolio-marquee .portfolio-card {
                    width: 14.5rem;
                    min-height: 11rem;
                }

                .portfolio-badge {
                    min-height: 1.85rem;
                    padding: 0.34rem 0.82rem 0.34rem 0.68rem;
                    font-size: 0.64rem;
                }

                .portfolio-site-card {
                    min-height: 18rem;
                }

                .portfolio-site-copy {
                    padding: 5.5rem 1rem 1rem;
                }

                .portfolio-site-link {
                    right: 1rem;
                    bottom: 1rem;
                }

                .portfolio-site-image {
                    object-fit: cover;
                }

                .portfolio-progress {
                    left: auto;
                    right: 1rem;
                    top: auto;
                    bottom: 4.75rem;
                    transform: none;
                    flex-direction: row;
                }

                .portfolio-footer-note {
                    left: 50%;
                    right: auto;
                    bottom: 1rem;
                    font-size: 0.68rem;
                }

                .portfolio-reveal-footer {
                    gap: 0.45rem;
                    min-height: 5rem;
                    padding: 1rem 0.85rem 1.2rem;
                    flex-wrap: wrap;
                }

                .portfolio-reveal-footer-text {
                    font-size: 0.52rem;
                    letter-spacing: 0.08em;
                }

                .portfolio-palette-tag {
                    right: 0.85rem;
                    left: auto;
                    top: 0.85rem;
                    bottom: auto;
                    min-width: 8.6rem;
                    padding: 0.42rem 0.48rem;
                    background: rgba(8, 12, 18, 0.28);
                    backdrop-filter: blur(18px);
                }

                .portfolio-palette-grid {
                    grid-template-columns: 1fr;
                    gap: 0.28rem;
                }

                .portfolio-palette-item {
                    padding: 0.28rem 0.34rem;
                    gap: 0.24rem 0.4rem;
                    background: rgba(255, 255, 255, 0.04);
                }

                .portfolio-palette-label,
                .portfolio-palette-code {
                    font-size: 0.54rem;
                    line-height: 1.1;
                }

                .portfolio-palette-swatch {
                    width: 0.42rem;
                    height: 0.42rem;
                }
            }
        </style>

        <div class="portfolio-progress" aria-hidden="true">
            <span class="portfolio-progress-dot is-active"></span>
            <span class="portfolio-progress-dot"></span>
            <span class="portfolio-progress-dot"></span>
            <span class="portfolio-progress-dot"></span>
            <span class="portfolio-progress-dot"></span>
        </div>

        <main aria-label="Portfolio scroll template" class="portfolio-shell" style="padding-bottom: 6rem;">
            <section class="portfolio-section is-active" data-color-one-name="Petrol Blue" data-color-one="#326586" data-color-two-name="Sand Mist" data-color-two="#F4E9D4" data-bg="#326586" data-fg="#F4E9D4" style="background: #326586; --page-fg: #F4E9D4;">
                <div class="portfolio-stage">
                    <div class="portfolio-motion-layer" aria-hidden="true">
                        <span class="portfolio-motion-grid" data-motion-type="grid" data-motion-speed="0.00006" data-motion-range-x="18" data-motion-range-y="12" data-motion-phase="0.2"></span>
                        <span class="portfolio-motion-blob" data-motion-type="blob" data-motion-speed="0.00018" data-motion-range-x="34" data-motion-range-y="20" data-motion-phase="0.3" style="left: 8%; top: 16%; width: 18rem; height: 18rem; background: color-mix(in srgb, #F4E9D4 70%, transparent);"></span>
                        <span class="portfolio-motion-blob" data-motion-type="blob" data-motion-speed="0.00013" data-motion-range-x="20" data-motion-range-y="14" data-motion-phase="1.4" style="right: 10%; bottom: 14%; width: 12rem; height: 12rem; background: color-mix(in srgb, #F4E9D4 42%, transparent);"></span>
                        <span class="portfolio-motion-ring" data-motion-type="ring" data-motion-speed="0.00012" data-motion-range-x="18" data-motion-range-y="14" data-motion-phase="0.8" style="right: 18%; top: 22%; width: 20rem; height: 20rem;"></span>
                    </div>
                    <div class="portfolio-frame">
                        <p class="portfolio-kicker">Web Developer</p>
                        <h1 class="portfolio-title">Azizul Azree</h1>
                        <p class="portfolio-copy">I work mainly across Shopify, WordPress, Laravel, PHP, MySQL, and Codex.</p>
                        <p class="portfolio-copy">Based in Kajang, Selangor, Malaysia.</p>
                    </div>
                </div>
            </section>

            <section class="portfolio-section" data-color-one-name="Muted Pine" data-color-one="#455B51" data-color-two-name="Sunlit Cream" data-color-two="#FFF0A4" data-bg="#455B51" data-fg="#FFF0A4" style="background: #455B51; --page-fg: #FFF0A4;">
                <div class="portfolio-stage">
                    <div class="portfolio-motion-layer" aria-hidden="true">
                        <span class="portfolio-motion-panel" data-motion-type="panel" data-motion-speed="0.00011" data-motion-range-x="12" data-motion-range-y="18" data-motion-phase="0.2" style="left: 9%; top: 18%; width: 11rem; height: 16rem;"></span>
                        <span class="portfolio-motion-panel" data-motion-type="panel" data-motion-speed="0.00009" data-motion-range-x="18" data-motion-range-y="14" data-motion-phase="1.1" style="right: 11%; top: 14%; width: 15rem; height: 21rem;"></span>
                        <span class="portfolio-motion-panel" data-motion-type="panel" data-motion-speed="0.00013" data-motion-range-x="14" data-motion-range-y="16" data-motion-phase="2.1" style="left: 30%; bottom: 14%; width: 12rem; height: 14rem;"></span>
                        <span class="portfolio-motion-panel" data-motion-type="panel" data-motion-speed="0.0001" data-motion-range-x="10" data-motion-range-y="12" data-motion-phase="2.8" style="right: 24%; bottom: 18%; width: 9rem; height: 11rem;"></span>
                    </div>
                    <div
                        data-carousel
                        class="portfolio-frame"
                    >
                        <div class="portfolio-current-layout">
                            <div class="portfolio-current-meta">
                                <p class="portfolio-current-label">E-commerce &amp; Shopify</p>
                                <h2 class="portfolio-current-role">Web Developer</h2>
                                <p class="portfolio-current-company">Jakel Wholesale &amp; Distribution Centre</p>
                                <p class="portfolio-current-date">Since Feb 2026</p>
                            </div>

                            <div class="portfolio-current-carousel">
                                <div class="portfolio-carousel">
                                    <div class="portfolio-carousel-track" data-carousel-track>
                                        <div class="portfolio-carousel-slide">
                                            <article class="portfolio-site-card">
                                                <div class="portfolio-site-media">
                                                    <picture>
                                                        <source srcset="{{ asset('site-thumbs/hajra-mobile.jpg') }}" media="(max-width: 768px)">
                                                        <img class="portfolio-site-image" src="{{ asset('site-thumbs/hajra.jpg') }}" alt="Galeri Hajra homepage preview" data-pan-image>
                                                    </picture>
                                                </div>

                                                <div class="portfolio-site-copy">
                                                    <p class="portfolio-site-index">Storefront</p>
                                                    <h3 class="portfolio-site-title">Galeri Hajra</h3>
                                                    <p class="portfolio-site-domain">galerihajra.com</p>
                                                </div>

                                                <a href="https://galerihajra.com/" target="_blank" rel="noreferrer" class="portfolio-site-link">
                                                    <span>Open site</span>
                                                </a>
                                            </article>
                                        </div>

                                        <div class="portfolio-carousel-slide">
                                            <article class="portfolio-site-card">
                                                <div class="portfolio-site-media">
                                                    <picture>
                                                        <source srcset="{{ asset('site-thumbs/aafya-mobile.jpg') }}" media="(max-width: 768px)">
                                                        <img class="portfolio-site-image" src="{{ asset('site-thumbs/aafya.jpg') }}" alt="Aafya.Co homepage preview" data-pan-image>
                                                    </picture>
                                                </div>

                                                <div class="portfolio-site-copy">
                                                    <p class="portfolio-site-index">Storefront</p>
                                                    <h3 class="portfolio-site-title">Aafya.Co</h3>
                                                    <p class="portfolio-site-domain">aafya.com.my</p>
                                                </div>

                                                <a href="https://aafya.com.my/" target="_blank" rel="noreferrer" class="portfolio-site-link">
                                                    <span>Open site</span>
                                                </a>
                                            </article>
                                        </div>

                                        <div class="portfolio-carousel-slide">
                                            <article class="portfolio-site-card">
                                                <div class="portfolio-site-media">
                                                    <picture>
                                                        <source srcset="{{ asset('site-thumbs/nlc-mobile.jpg') }}" media="(max-width: 768px)">
                                                        <img class="portfolio-site-image" src="{{ asset('site-thumbs/nlc.jpg') }}" alt="No Label Clothing homepage preview" data-pan-image>
                                                    </picture>
                                                </div>

                                                <div class="portfolio-site-copy">
                                                    <p class="portfolio-site-index">Storefront</p>
                                                    <h3 class="portfolio-site-title">No Label Clothing</h3>
                                                    <p class="portfolio-site-domain">nolabelclothing.com.my</p>
                                                </div>

                                                <a href="https://nolabelclothing.com.my/" target="_blank" rel="noreferrer" class="portfolio-site-link">
                                                    <span>Open site</span>
                                                </a>
                                            </article>
                                        </div>
                                    </div>
                                </div>

                                <div class="portfolio-carousel-footer">
                                    <button type="button" class="portfolio-carousel-button" data-carousel-prev aria-label="Previous site">←</button>
                                    <p class="portfolio-carousel-count" data-carousel-count>1 / 3</p>
                                    <button type="button" class="portfolio-carousel-button" data-carousel-next aria-label="Next site">→</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="portfolio-section" data-color-one-name="Soft Coral" data-color-one="#E8B59E" data-color-two-name="Onyx Smoke" data-color-two="#2C2C2C" data-bg="#E8B59E" data-fg="#2C2C2C" style="background: #E8B59E; --page-fg: #2C2C2C;">
                <div class="portfolio-stage">
                    <div class="portfolio-motion-layer" aria-hidden="true">
                        <span class="portfolio-motion-orbiter" data-motion-type="orbiter" data-motion-speed="0.00012" data-motion-phase="0.2" data-motion-orbit-radius="5.25" style="left: 10%; top: 18%;"></span>
                        <span class="portfolio-motion-orbiter" data-motion-type="orbiter" data-motion-speed="0.00008" data-motion-phase="1.6" data-motion-orbit-radius="4.25" style="right: 14%; bottom: 18%; width: 10rem; height: 10rem;"></span>
                        <span class="portfolio-motion-orbiter" data-motion-type="orbiter" data-motion-speed="0.0001" data-motion-phase="2.4" data-motion-orbit-radius="3.4" style="left: 42%; top: 26%; width: 8rem; height: 8rem;"></span>
                    </div>
                    <div class="portfolio-frame">
                        <p class="portfolio-kicker">Laravel, PHP &amp; MySQL</p>
                        <h2 class="portfolio-title">Infrastructure</h2>
                        <p class="portfolio-copy">This is the side of the work people usually do not see: keeping older systems going, rebuilding the parts that need replacing, and sorting out the logic behind daily use.</p>
                        <div class="portfolio-marquee">
                            <div class="portfolio-marquee-track">
                                <div class="portfolio-card">
                                    <p class="portfolio-card-label">PHP/MySQL CRM</p>
                                    <p class="portfolio-card-copy">Looked after the existing CRM, fixed issues, and kept it usable day to day.</p>
                                </div>
                                <div class="portfolio-card">
                                    <p class="portfolio-card-label">Laravel 12</p>
                                    <p class="portfolio-card-copy">Built replacement flows in Laravel 12 where the older structure no longer fit.</p>
                                </div>
                                <div class="portfolio-card">
                                    <p class="portfolio-card-label">Workflow</p>
                                    <p class="portfolio-card-copy">Moved parts of the work from older patterns into something easier to manage.</p>
                                </div>
                                <div class="portfolio-card">
                                    <p class="portfolio-card-label">Maintenance</p>
                                    <p class="portfolio-card-copy">Handled fixes, small changes, and the usual upkeep on live internal systems.</p>
                                </div>
                                <div class="portfolio-card">
                                    <p class="portfolio-card-label">Rebuilds</p>
                                    <p class="portfolio-card-copy">Reworked older parts into cleaner Laravel-based builds where needed.</p>
                                </div>
                                <div class="portfolio-card">
                                    <p class="portfolio-card-label">Scope</p>
                                    <p class="portfolio-card-copy">Worked across internal logic, process flow, and the tools people relied on.</p>
                                </div>
                                <div class="portfolio-card" aria-hidden="true">
                                    <p class="portfolio-card-label">PHP/MySQL CRM</p>
                                    <p class="portfolio-card-copy">Looked after the existing CRM, fixed issues, and kept it usable day to day.</p>
                                </div>
                                <div class="portfolio-card" aria-hidden="true">
                                    <p class="portfolio-card-label">Laravel 12</p>
                                    <p class="portfolio-card-copy">Built replacement flows in Laravel 12 where the older structure no longer fit.</p>
                                </div>
                                <div class="portfolio-card" aria-hidden="true">
                                    <p class="portfolio-card-label">Workflow</p>
                                    <p class="portfolio-card-copy">Moved parts of the work from older patterns into something easier to manage.</p>
                                </div>
                                <div class="portfolio-card" aria-hidden="true">
                                    <p class="portfolio-card-label">Maintenance</p>
                                    <p class="portfolio-card-copy">Handled fixes, small changes, and the usual upkeep on live internal systems.</p>
                                </div>
                                <div class="portfolio-card" aria-hidden="true">
                                    <p class="portfolio-card-label">Rebuilds</p>
                                    <p class="portfolio-card-copy">Reworked older parts into cleaner Laravel-based builds where needed.</p>
                                </div>
                                <div class="portfolio-card" aria-hidden="true">
                                    <p class="portfolio-card-label">Scope</p>
                                    <p class="portfolio-card-copy">Worked across internal logic, process flow, and the tools people relied on.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="portfolio-section" data-color-one-name="Soft Stone" data-color-one="#F0EBE9" data-color-two-name="Fresh Pine" data-color-two="#36A372" data-bg="#36A372" data-fg="#F0EBE9" style="background: #36A372; --page-fg: #F0EBE9;">
                <div class="portfolio-stage">
                    <div class="portfolio-motion-layer" aria-hidden="true">
                        <span class="portfolio-motion-wave" data-motion-type="wave" data-motion-speed="0.00011" data-motion-range-x="8" data-motion-range-y="18" data-motion-phase="0.4" style="top: 16%;"></span>
                        <span class="portfolio-motion-wave" data-motion-type="wave" data-motion-speed="0.00014" data-motion-range-x="10" data-motion-range-y="16" data-motion-phase="1.5" style="top: 42%; height: 20rem;"></span>
                        <span class="portfolio-motion-wave" data-motion-type="wave" data-motion-speed="0.00009" data-motion-range-x="6" data-motion-range-y="14" data-motion-phase="2.4" style="top: 68%; height: 12rem;"></span>
                        <span class="portfolio-motion-wave" data-motion-type="wave" data-motion-speed="0.00012" data-motion-range-x="9" data-motion-range-y="12" data-motion-phase="3.2" style="top: 30%; height: 8rem;"></span>
                    </div>
                    <div class="portfolio-frame">
                        <p class="portfolio-kicker">Previous Roles</p>
                        <h2 class="portfolio-title">Previous Work</h2>
                        <p class="portfolio-copy">Work across WordPress, Laravel, internal systems, support, and web applications.</p>
                        <div class="portfolio-grid">
                            <div class="portfolio-card portfolio-card-tall" style="grid-column: span 3 / span 3;">
                                <p class="portfolio-card-label">K Al Walid Group</p>
                                <p class="portfolio-card-copy">Web Developer &amp; IT Executive</p>
                                <p class="portfolio-card-meta">2025 — 2026</p>
                                <div class="portfolio-badge-row">
                                    <span class="portfolio-badge">WordPress</span>
                                    <span class="portfolio-badge">Laravel</span>
                                    <span class="portfolio-badge">PHP</span>
                                    <span class="portfolio-badge">MySQL</span>
                                </div>
                            </div>
                            <div class="portfolio-card portfolio-card-tall" style="grid-column: span 3 / span 3;">
                                <p class="portfolio-card-label">Bio Fluid Sdn Bhd</p>
                                <p class="portfolio-card-copy">Website Developer &amp; Executive IT Support</p>
                                <p class="portfolio-card-meta">2022 — 2025</p>
                                <div class="portfolio-badge-row">
                                    <span class="portfolio-badge">WordPress</span>
                                    <span class="portfolio-badge">Multiple Sites</span>
                                    <span class="portfolio-badge">Custom Features</span>
                                    <span class="portfolio-badge">IT Support</span>
                                </div>
                            </div>
                            <div class="portfolio-card portfolio-card-tall" style="grid-column: span 3 / span 3;">
                                <p class="portfolio-card-label">Honsbridge International School</p>
                                <p class="portfolio-card-copy">Web Developer cum IT/Admin Support</p>
                                <p class="portfolio-card-meta">2019 — 2022</p>
                                <div class="portfolio-badge-row">
                                    <span class="portfolio-badge">WordPress</span>
                                    <span class="portfolio-badge">Internal Systems</span>
                                    <span class="portfolio-badge">Staff Support</span>
                                </div>
                            </div>
                            <div class="portfolio-card portfolio-card-tall" style="grid-column: span 3 / span 3;">
                                <p class="portfolio-card-label">Tabir Omega Sdn Bhd</p>
                                <p class="portfolio-card-copy">Web Developer</p>
                                <p class="portfolio-card-meta">2018 — 2019</p>
                                <div class="portfolio-badge-row">
                                    <span class="portfolio-badge">Web Applications</span>
                                    <span class="portfolio-badge">Frontend</span>
                                    <span class="portfolio-badge">Backend</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="portfolio-section portfolio-contact-section" data-color-one-name="Citrus Glow" data-color-one="#E6FF55" data-color-two-name="Dark Jade" data-color-two="#042D22" data-bg="#042D22" data-fg="#E6FF55" style="background: #042D22; --page-fg: #E6FF55;">
                <div class="portfolio-stage">
                    <div class="portfolio-motion-layer" aria-hidden="true">
                        <span class="portfolio-motion-blob" data-motion-type="blob" data-motion-speed="0.00021" data-motion-range-x="28" data-motion-range-y="18" data-motion-phase="0.5" style="left: 16%; bottom: 18%; width: 14rem; height: 14rem; background: color-mix(in srgb, #E6FF55 58%, transparent); opacity: 0.14;"></span>
                        <span class="portfolio-motion-ring" data-motion-type="ring" data-motion-speed="0.0001" data-motion-range-x="14" data-motion-range-y="14" data-motion-phase="1.2" style="right: 28%; bottom: 16%; width: 16rem; height: 16rem;"></span>
                        <span class="portfolio-motion-grid" data-motion-type="grid" data-motion-speed="0.00004" data-motion-range-x="10" data-motion-range-y="8" data-motion-phase="1.4" style="background-size: 3rem 3rem; opacity: 0.1;"></span>
                        <span class="portfolio-motion-blob" data-motion-type="blob" data-motion-speed="0.00014" data-motion-range-x="16" data-motion-range-y="14" data-motion-phase="2.2" style="right: 12%; top: 14%; width: 10rem; height: 10rem; background: color-mix(in srgb, #E6FF55 32%, transparent); opacity: 0.18;"></span>
                    </div>
                    <div class="portfolio-frame">
                        <p class="portfolio-kicker">Links &amp; Contact</p>
                        <h2 class="portfolio-title">Contact Endcap</h2>
                        <p class="portfolio-copy">A few ways to reach me and a couple of places where my work lives.</p>
                        <div class="portfolio-link-list">
                            <a class="portfolio-link-item" href="mailto:azizulazree@gmail.com">
                                <p class="portfolio-link-label">Email</p>
                                <p class="portfolio-link-value">azizulazree@gmail.com</p>
                            </a>
                            <a class="portfolio-link-item" href="tel:+601116857378">
                                <p class="portfolio-link-label">Phone</p>
                                <p class="portfolio-link-value">+60 11-1685 7378</p>
                            </a>
                            <a class="portfolio-link-item" href="https://github.com/AzizulAzree" target="_blank" rel="noreferrer">
                                <p class="portfolio-link-label">GitHub</p>
                                <p class="portfolio-link-value">github.com/AzizulAzree</p>
                            </a>
                            <a class="portfolio-link-item" href="https://www.linkedin.com/in/azizul-azree/" target="_blank" rel="noreferrer">
                                <p class="portfolio-link-label">LinkedIn</p>
                                <p class="portfolio-link-value">linkedin.com/in/azizul-azree</p>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <footer class="portfolio-reveal-footer" aria-label="Footer credits">
            <p class="portfolio-reveal-footer-text">Powered by Google Cloud</p>
            <span class="portfolio-reveal-footer-sep" aria-hidden="true"></span>
            <p class="portfolio-reveal-footer-text">Made by Codex</p>
            <span class="portfolio-reveal-footer-sep" aria-hidden="true"></span>
            <p class="portfolio-reveal-footer-text">Directed by me</p>
            <span class="portfolio-reveal-footer-sep" aria-hidden="true"></span>
            <p class="portfolio-reveal-footer-text">2026</p>
        </footer>

        <div class="portfolio-footer-note">Scroll to explore</div>
        <aside class="portfolio-palette-tag" aria-live="polite">
            <div class="portfolio-palette-grid">
                <div class="portfolio-palette-item">
                    <span class="portfolio-palette-swatch" data-palette-color-one-swatch style="background: #326586;"></span>
                    <p class="portfolio-palette-label" data-palette-color-one-name>Petrol Blue</p>
                    <p class="portfolio-palette-code" data-palette-color-one-code>#326586</p>
                </div>
                <div class="portfolio-palette-item">
                    <span class="portfolio-palette-swatch" data-palette-color-two-swatch style="background: #F4E9D4;"></span>
                    <p class="portfolio-palette-label" data-palette-color-two-name>Sand Mist</p>
                    <p class="portfolio-palette-code" data-palette-color-two-code>#F4E9D4</p>
                </div>
            </div>
        </aside>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sections = Array.from(document.querySelectorAll('.portfolio-section'));
                const dots = Array.from(document.querySelectorAll('.portfolio-progress-dot'));
                const motionNodes = Array.from(document.querySelectorAll('[data-motion-speed]'));
                const paletteTag = document.querySelector('.portfolio-palette-tag');
                const colorOneSwatch = paletteTag?.querySelector('[data-palette-color-one-swatch]');
                const colorTwoSwatch = paletteTag?.querySelector('[data-palette-color-two-swatch]');
                const colorOneName = paletteTag?.querySelector('[data-palette-color-one-name]');
                const colorTwoName = paletteTag?.querySelector('[data-palette-color-two-name]');
                const colorOneCode = paletteTag?.querySelector('[data-palette-color-one-code]');
                const colorTwoCode = paletteTag?.querySelector('[data-palette-color-two-code]');
                const contactSection = document.querySelector('.portfolio-contact-section');
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                let activeIndex = 0;
                let isAnimating = false;
                let touchStartY = null;
                let animationTimer = null;
                let motionFrame = null;

                const hexToRgb = (hex) => {
                    const normalized = hex.replace('#', '');

                    if (normalized.length !== 6) {
                        return null;
                    }

                    const int = Number.parseInt(normalized, 16);

                    if (Number.isNaN(int)) {
                        return null;
                    }

                    return {
                        r: (int >> 16) & 255,
                        g: (int >> 8) & 255,
                        b: int & 255,
                    };
                };

                const setPaletteTag = (section) => {
                    if (! section || ! paletteTag) {
                        return;
                    }

                    const colorOneNameValue = section.dataset.colorOneName || 'Color One';
                    const colorTwoNameValue = section.dataset.colorTwoName || 'Color Two';
                    const colorOneValue = section.dataset.colorOne || '#000000';
                    const colorTwoValue = section.dataset.colorTwo || '#FFFFFF';
                    const bg = section.dataset.bg || '#000000';
                    const fg = section.dataset.fg || '#FFFFFF';
                    const bgRgb = hexToRgb(bg);

                    colorOneSwatch.style.background = colorOneValue;
                    colorTwoSwatch.style.background = colorTwoValue;
                    colorOneName.textContent = colorOneNameValue;
                    colorTwoName.textContent = colorTwoNameValue;
                    colorOneCode.textContent = colorOneValue;
                    colorTwoCode.textContent = colorTwoValue;
                    paletteTag.style.color = fg;
                    paletteTag.style.borderColor = `rgba(${bgRgb?.r ?? 255}, ${bgRgb?.g ?? 255}, ${bgRgb?.b ?? 255}, 0.28)`;
                    paletteTag.style.background = bgRgb
                        ? `rgba(${bgRgb.r}, ${bgRgb.g}, ${bgRgb.b}, 0.26)`
                        : 'rgba(10, 10, 10, 0.22)';
                };

                const setActiveSection = (nextIndex) => {
                    sections.forEach((section, index) => {
                        section.classList.toggle('is-active', index === nextIndex);
                    });

                    dots.forEach((dot, index) => {
                        dot.classList.toggle('is-active', index === nextIndex);
                    });

                    activeIndex = nextIndex;
                    setPaletteTag(sections[nextIndex]);
                };

                const updateContactParallax = () => {
                    if (! contactSection) {
                        return;
                    }

                    const rect = contactSection.getBoundingClientRect();
                    const viewport = window.innerHeight || 1;
                    const progress = Math.min(1, Math.max(0, (viewport - rect.bottom + 96) / Math.max(1, 160)));
                    const lift = prefersReducedMotion ? 0 : progress * 72;

                    contactSection.style.setProperty('--section-lift', `${lift.toFixed(2)}px`);
                };

                const observer = new IntersectionObserver(
                    (entries) => {
                        const visibleEntries = entries
                            .filter((entry) => entry.isIntersecting)
                            .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

                        if (! visibleEntries.length) {
                            return;
                        }

                        const nextIndex = sections.indexOf(visibleEntries[0].target);

                        if (nextIndex >= 0) {
                            setActiveSection(nextIndex);
                        }
                    },
                    {
                        threshold: [0.3, 0.45, 0.6, 0.75],
                    }
                );

                const releaseAnimationLock = () => {
                    window.clearTimeout(animationTimer);
                    animationTimer = window.setTimeout(() => {
                        isAnimating = false;
                    }, prefersReducedMotion ? 0 : 900);
                };

                const goToSection = (nextIndex) => {
                    const boundedIndex = Math.max(0, Math.min(nextIndex, sections.length - 1));

                    if (boundedIndex === activeIndex || isAnimating) {
                        return;
                    }

                    isAnimating = true;
                    setActiveSection(boundedIndex);
                    sections[boundedIndex].scrollIntoView({
                        behavior: prefersReducedMotion ? 'auto' : 'smooth',
                        block: 'start',
                    });
                    releaseAnimationLock();
                };

                const handleDirectionalScroll = (direction) => {
                    if (direction === 0) {
                        return false;
                    }

                    const nextIndex = activeIndex + direction;

                    if (nextIndex < 0 || nextIndex >= sections.length) {
                        return false;
                    }

                    goToSection(nextIndex);
                    return true;
                };

                window.addEventListener('wheel', (event) => {
                    if (Math.abs(event.deltaY) < 12) {
                        return;
                    }

                    const handled = handleDirectionalScroll(event.deltaY > 0 ? 1 : -1);

                    if (handled) {
                        event.preventDefault();
                    }
                }, { passive: false });

                window.addEventListener('keydown', (event) => {
                    if (['ArrowDown', 'PageDown', ' '].includes(event.key)) {
                        const handled = handleDirectionalScroll(1);

                        if (handled) {
                            event.preventDefault();
                        }
                    }

                    if (['ArrowUp', 'PageUp'].includes(event.key)) {
                        const handled = handleDirectionalScroll(-1);

                        if (handled) {
                            event.preventDefault();
                        }
                    }

                    if (event.key === 'Home') {
                        event.preventDefault();
                        goToSection(0);
                    }

                    if (event.key === 'End') {
                        event.preventDefault();
                        goToSection(sections.length - 1);
                    }
                });

                window.addEventListener('touchstart', (event) => {
                    touchStartY = event.touches[0]?.clientY ?? null;
                }, { passive: true });

                window.addEventListener('touchend', (event) => {
                    if (touchStartY === null) {
                        return;
                    }

                    const touchEndY = event.changedTouches[0]?.clientY ?? touchStartY;
                    const deltaY = touchStartY - touchEndY;
                    touchStartY = null;

                    if (Math.abs(deltaY) < 40) {
                        return;
                    }

                    handleDirectionalScroll(deltaY > 0 ? 1 : -1);
                }, { passive: true });

                window.addEventListener('scroll', updateContactParallax, { passive: true });

                if (! prefersReducedMotion && motionNodes.length) {
                    const animateMotion = (time) => {
                        motionNodes.forEach((node) => {
                            const motionType = node.dataset.motionType || 'blob';
                            const speed = Number(node.dataset.motionSpeed || 0.00015);
                            const rangeX = Number(node.dataset.motionRangeX || 20);
                            const rangeY = Number(node.dataset.motionRangeY || 20);
                            const phase = Number(node.dataset.motionPhase || 0);
                            const driftX = Math.sin(time * speed + phase) * rangeX;
                            const driftY = Math.cos(time * speed * 0.82 + phase) * rangeY;
                            const scale = 1 + Math.sin(time * speed * 0.6 + phase) * 0.035;

                            if (motionType === 'grid') {
                                node.style.setProperty('--grid-x', `${driftX}px`);
                                node.style.setProperty('--grid-y', `${driftY}px`);
                                return;
                            }

                            if (motionType === 'bar') {
                                const barScale = 0.86 + Math.sin(time * speed * 1.1 + phase) * 0.14;
                                node.style.setProperty('--bar-x', `${driftX}px`);
                                node.style.setProperty('--bar-y', `${driftY}px`);
                                node.style.setProperty('--bar-scale', barScale.toFixed(4));
                                return;
                            }

                            if (motionType === 'panel') {
                                const panelRotate = Math.sin(time * speed * 0.7 + phase) * 4;
                                node.style.setProperty('--panel-x', `${driftX}px`);
                                node.style.setProperty('--panel-y', `${driftY}px`);
                                node.style.setProperty('--panel-rotate', `${panelRotate.toFixed(3)}deg`);
                                return;
                            }

                            if (motionType === 'orbiter') {
                                const orbitAngle = (time * speed * 32 + phase * 120) % 360;
                                const orbitRadius = Number(node.dataset.motionOrbitRadius || 5.75);
                                node.style.setProperty('--orbit-angle', `${orbitAngle}deg`);
                                node.style.setProperty('--orbit-radius', `${orbitRadius}rem`);
                                return;
                            }

                            if (motionType === 'wave') {
                                const waveScale = 0.92 + Math.sin(time * speed * 0.9 + phase) * 0.1;
                                node.style.setProperty('--wave-x', `${driftX}px`);
                                node.style.setProperty('--wave-y', `${driftY}px`);
                                node.style.setProperty('--wave-scale', waveScale.toFixed(4));
                                return;
                            }

                            if (motionType === 'ring') {
                                node.style.setProperty('--ring-x', `${driftX}px`);
                                node.style.setProperty('--ring-y', `${driftY}px`);
                                node.style.setProperty('--ring-scale', scale.toFixed(4));
                                return;
                            }

                            node.style.setProperty('--blob-x', `${driftX}px`);
                            node.style.setProperty('--blob-y', `${driftY}px`);
                            node.style.setProperty('--blob-scale', scale.toFixed(4));
                        });

                        motionFrame = window.requestAnimationFrame(animateMotion);
                    };

                    motionFrame = window.requestAnimationFrame(animateMotion);

                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden && motionFrame) {
                            window.cancelAnimationFrame(motionFrame);
                            motionFrame = null;
                            return;
                        }

                        if (! document.hidden && ! motionFrame) {
                            motionFrame = window.requestAnimationFrame(animateMotion);
                        }
                    });
                }

                sections.forEach((section) => observer.observe(section));
                setActiveSection(0);
                updateContactParallax();

                document.querySelectorAll('[data-carousel]').forEach((carouselRoot) => {
                    const track = carouselRoot.querySelector('[data-carousel-track]');
                    const prevButton = carouselRoot.querySelector('[data-carousel-prev]');
                    const nextButton = carouselRoot.querySelector('[data-carousel-next]');
                    const count = carouselRoot.querySelector('[data-carousel-count]');
                    const slides = Array.from(carouselRoot.querySelectorAll('.portfolio-carousel-slide'));

                    if (! track || ! prevButton || ! nextButton || ! count || ! slides.length) {
                        return;
                    }

                    let carouselIndex = 0;
                    const totalSlides = slides.length;

                    const renderCarousel = () => {
                        track.style.transform = `translateX(-${carouselIndex * 100}%)`;
                        count.textContent = `${carouselIndex + 1} / ${totalSlides}`;
                    };

                    prevButton.addEventListener('click', () => {
                        carouselIndex = (carouselIndex - 1 + totalSlides) % totalSlides;
                        renderCarousel();
                    });

                    nextButton.addEventListener('click', () => {
                        carouselIndex = (carouselIndex + 1) % totalSlides;
                        renderCarousel();
                    });

                    renderCarousel();
                });

                const refreshPanImages = () => {
                    document.querySelectorAll('[data-pan-image]').forEach((image) => {
                        const frame = image.closest('.portfolio-site-media');

                        if (! frame || ! image.complete || image.naturalHeight === 0) {
                            return;
                        }

                        const frameHeight = frame.clientHeight;
                        const renderedHeight = image.clientHeight;
                        const overflow = Math.max(0, renderedHeight - frameHeight);
                        const duration = Math.max(12, Math.min(30, overflow / 22));

                        frame.classList.remove('has-pan');
                        image.style.animation = 'none';
                        image.style.transform = 'translateY(0)';
                        image.style.removeProperty('--pan-distance');
                        image.style.removeProperty('--pan-duration');

                        if (prefersReducedMotion || overflow <= 0) {
                            return;
                        }

                        image.style.setProperty('--pan-distance', `${overflow}px`);
                        image.style.setProperty('--pan-duration', `${duration}s`);

                        window.requestAnimationFrame(() => {
                            frame.classList.add('has-pan');
                            image.style.animation = '';
                        });
                    });
                };

                window.addEventListener('load', refreshPanImages);
                window.addEventListener('resize', refreshPanImages);
                window.addEventListener('resize', updateContactParallax);
                document.querySelectorAll('[data-pan-image]').forEach((image) => {
                    image.addEventListener('load', refreshPanImages);
                });
            });
        </script>
    </body>
</html>
