    <!-- Core JavaScript (Must load first) -->
    <script src="<?php echo basePath('assets/scripts/jquery.min.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/bootstrap/js/bootstrap.min.js'); ?>"></script>
    <script src="<?php echo basePath('assets/scripts/modernizr.min.js'); ?>"></script>
    
    <!-- Highcharts -->
    <script src="<?php echo basePath('assets/plugin/chart/highcharts/highcharts.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/chart/highcharts/highcharts-3d.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/chart/highcharts/themes/grid-light.js'); ?>"></script>
    
    <!-- Plugins -->
    <script src="<?php echo basePath('assets/plugin/nprogress/nprogress.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/fullscreen/jquery.fullscreen-min.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/waves/waves.min.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/moment/moment.js'); ?>"></script>
    <script src="<?php echo basePath('assets/plugin/percircle/js/percircle.js'); ?>"></script>
    
    <!-- Custom Scripts (Load after plugins) -->
    <script src="<?php echo basePath('assets/scripts/main.min.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script src="<?php echo basePath('assets/scripts/chart.highcharts.init.min.js'); ?>"></script>
    <script src="<?php echo basePath('assets/scripts/scs/charts.js'); ?>"></script>
    <script src="<?php echo basePath('assets/scripts/scs/questions.js'); ?>"></script>
    
    <!-- Export Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
     <?php
    if (isset($currentPage) && $currentPage === 'student-survey') {
        // Load student-specific JS
        echo '<script src="' . basePath('assets/scripts/scs/student-survey.js') . '"></script>';
    } else {
        // Load default survey JS for other pages (guardian, staff, etc.)
        echo '<script src="' . basePath('assets/scripts/scs/survey.js') . '"></script>';
    }
    ?>
    <?php if (strpos($currentPage, 'analytics') !== false): ?>
        <?php if ($currentPage === 'yearly-analytics'): ?>
            <script src="<?php echo basePath('assets/scripts/scs/yearly-export.js'); ?>"></script>
        <?php else: ?>
            <script src="<?php echo basePath('assets/scripts/scs/export.js'); ?>"></script>
        <?php endif; ?>
    <?php endif; ?>
    
    <script src="<?php echo basePath('assets/scripts/scs/yearly-charts.js'); ?>"></script>
    <?php if (isset($currentPage)): ?>
        <?php if ($currentPage === 'yearly-analytics'): ?>
            <script src="<?php echo basePath('assets/scripts/scs/yearly-export.js'); ?>"></script>
        <?php elseif (strpos($currentPage, '-analytics') !== false): ?>
            <script src="<?php echo basePath('assets/scripts/scs/export.js'); ?>"></script>
        <?php endif; ?>
    <?php endif; ?>
    
    <script src="<?php echo basePath('assets/scripts/scs/yearly-charts.js'); ?>"></script>
    
    <!-- Export button handlers -->
    <!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pdfBtn = document.getElementById('pdfExport');
        const excelBtn = document.getElementById('excelExport');
        
        if (pdfBtn) {
            pdfBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof exportToPDF === 'function') {
                    exportToPDF();
                }
            });
        }
        
        if (excelBtn) {
            excelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof exportToExcel === 'function') {
                    exportToExcel();
                }
            });
        }
    });
    </script> -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to initialize export buttons
        function initializeExportButtons() {
            const pdfBtn = document.getElementById('pdfExport');
            const excelBtn = document.getElementById('excelExport');
            const baseTitle = document.title || 'Survey Results'; // Default title

            if (pdfBtn) {
                pdfBtn.removeEventListener('click', handlePdfExport); // Remove previous listener if any
                pdfBtn.addEventListener('click', handlePdfExport);
            }

            if (excelBtn) {
                excelBtn.removeEventListener('click', handleExcelExport); // Remove previous listener if any
                excelBtn.addEventListener('click', handleExcelExport);
            }

             function handlePdfExport(e) {
                e.preventDefault();
                if (typeof exportToPDF === 'function') {
                    exportToPDF(baseTitle); // Pass title
                } else {
                    console.warn('exportToPDF function not found.');
                }
            }

             function handleExcelExport(e) {
                e.preventDefault();
                if (typeof exportToExcel === 'function') {
                    exportToExcel(baseTitle); // Pass title
                } else {
                     console.warn('exportToExcel function not found.');
                }
            }
        }
        initializeExportButtons();

        // If using AJAX navigation or dynamic content loading, you might need
        // to re-call initializeExportButtons() after content updates.
    });
    </script>
</body>
</html>