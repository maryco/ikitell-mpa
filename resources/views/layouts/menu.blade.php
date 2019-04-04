<!--main-menu-->
<transition name="animatecss-slide-h"
            enter-active-class="animated slideInRight fast"
            leave-active-class="animated slideOutRight faster"
            v-on:enter="changeMenuVisibility(true)">
    <div class="layout-main-menu" id="panelMainMenu" v-show="isMenuActive">
        @auth
        <ul>
            <li><a href="{{ route('home') }}">{{ __('label.menu.home') }}</a></li>
            <li><a href="{{ route('device.list') }}">
                    <svg role="img" class="icon">
                        <use xlink:href="#mobile"></use>
                    </svg>{{ __('label.menu.device') }}</a></li>
            <li><a href="{{ route('rule.list') }}">
                    <svg role="img" class="icon">
                        <use xlink:href="#alert"></use>
                    </svg>{{ __('label.menu.rule') }}</a></li>
            <li><a href="{{ route('notice.address.list') }}">
                    <svg role="img" class="icon">
                        <use xlink:href="#emails"></use>
                    </svg>{{ __('label.menu.notice_address') }}</a></li>
            <li><a href="{{ route('profile.edit') }}">
                    <svg role="img" class="icon">
                        <use xlink:href="#profile"></use>
                    </svg>{{ __('label.menu.profile') }}</a></li>
        </ul>
        <ul>
            <!-- TODO: 仮実装の修正 -->
            <li><a onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">{{ __('label.btn.logout') }}</a></li>
        </ul>
        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
        @else
        <ul>
            <li><a href="{{ route('login') }}">{{ __('label.btn.login') }}</a></li>
            <li><a href="{{ route('register') }}">{{ __('label.btn.register_account') }}</a></li>
        </ul>
        @endauth
    </div>
</transition>
