<!DOCTYPE html>
<html lang="en-US" dir="ltr">

<head>
    @include('partials.head')
</head>

<body>
    <main class="main" id="top">
        <div class="container" data-layout="container">
            <script>
                var container = document.querySelector('[data-layout]');
                if (container) {
                    container.classList.remove('container');
                    container.classList.add('container-fluid');
                }
            </script>

            @include('partials.sidebar')

            <div class="content">
                @include('partials.navbar')

                <div class="px-3">
                    @if (session('success'))
                        <div class="alert alert-success border-0">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger border-0">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>

                @include('partials.footer')
            </div>
        </div>
    </main>

    @include('partials.scripts')
    @stack('scripts')
</body>

</html>
