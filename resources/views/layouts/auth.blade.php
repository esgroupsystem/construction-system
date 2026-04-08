<!DOCTYPE html>
<html lang="en-US" dir="ltr">

<head>
    @include('partials.auth-head')
</head>

<body>
    <main class="main" id="top">
        <div class="container" data-layout="container">
            <script>
                var isFluid = JSON.parse(localStorage.getItem('isFluid'));
                if (isFluid) {
                    var container = document.querySelector('[data-layout]');
                    if (container) {
                        container.classList.remove('container');
                        container.classList.add('container-fluid');
                    }
                }
            </script>

            @yield('content')
        </div>
    </main>

    @include('partials.scripts')
    @stack('scripts')
</body>

</html>
