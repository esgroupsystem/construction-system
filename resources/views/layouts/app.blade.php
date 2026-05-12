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

                <div class="px-0">

                    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                        @if (session('success'))
                            <div class="toast align-items-center toast-success border-0 shadow-sm" role="alert">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        {{ session('success') }}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"></button>
                                </div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="toast align-items-center toast-error border-0 shadow-sm" role="alert">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        {{ session('error') }}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"></button>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger-custom border-0 shadow-sm">
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

    {{-- Global Confirm Modal --}}
    <div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="confirm-icon me-3">
                            <span class="fas fa-exclamation-triangle"></span>
                        </div>

                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="globalConfirmTitle">
                                Confirm Action
                            </h5>
                            <small class="text-muted">
                                Please confirm before continuing.
                            </small>
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>

                <div class="modal-body pt-3">
                    <p class="mb-0 text-muted" id="globalConfirmMessage">
                        Are you sure you want to continue?
                    </p>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-falcon-danger" id="globalConfirmBtn">
                        Confirm
                    </button>
                </div>

            </div>
        </div>
    </div>

    @include('partials.scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.toast').forEach(function(toastEl) {
                const toast = new bootstrap.Toast(toastEl, {
                    delay: 3000,
                    autohide: true
                });

                toast.show();
            });

            let targetForm = null;

            const modalElement = document.getElementById('globalConfirmModal');
            const confirmBtn = document.getElementById('globalConfirmBtn');
            const confirmTitle = document.getElementById('globalConfirmTitle');
            const confirmMessage = document.getElementById('globalConfirmMessage');

            if (modalElement && confirmBtn) {
                const confirmModal = new bootstrap.Modal(modalElement);

                document.querySelectorAll('.confirm-action').forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();

                        targetForm = this.closest('form');

                        confirmTitle.innerText = this.dataset.title || 'Confirm Action';
                        confirmMessage.innerText = this.dataset.message ||
                            'Are you sure you want to continue?';

                        confirmBtn.innerText = this.dataset.confirmText || 'Confirm';

                        confirmModal.show();
                    });
                });

                confirmBtn.addEventListener('click', function() {
                    if (targetForm) {
                        targetForm.submit();
                    }
                });
            }
        });
    </script>

    <style>
        /* Success Toast */
        .toast-success {
            background-color: #198754 !important;
            color: #fff;
        }

        /* Error Toast */
        .toast-error {
            background-color: #dc3545 !important;
            color: #fff;
        }

        /* Validation Errors */
        .alert-danger-custom {
            background-color: #f8d7da;
            color: #842029;
            border-left: 5px solid #dc3545;
        }

        /* Global Confirm Modal */
        .confirm-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: #fdecef;
            color: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
    </style>

    @stack('scripts')
</body>

</html>
