<?php if (isset($_SESSION['user_id'])): ?>
                </div> <!-- End of Main Content -->
            </div> <!-- End of min-h-screen flex -->
            <?php endif; ?>

            <!-- Common Scripts -->
            <script src="assets/js/main.js"></script>
            
            <!-- Success Message Toast -->
            <?php if (isset($success_message)): ?>
            <div id="successToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 translate-y-0">
                <?php echo $success_message; ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById('successToast').classList.add('translate-y-full');
                    setTimeout(() => {
                        document.getElementById('successToast').remove();
                    }, 300);
                }, 3000);
            </script>
            <?php endif; ?>

            <!-- Error Message Toast -->
            <?php if (isset($error_message)): ?>
            <div id="errorToast" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 translate-y-0">
                <?php echo $error_message; ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById('errorToast').classList.add('translate-y-full');
                    setTimeout(() => {
                        document.getElementById('errorToast').remove();
                    }, 300);
                }, 3000);
            </script>
            <?php endif; ?>
        </body>
    </html>
