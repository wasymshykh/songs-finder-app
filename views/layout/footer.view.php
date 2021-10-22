
    <?php if (isset($popper_js) && $popper_js): ?>
        <?=js_link('popper.min', true)?>
    <?php endif; ?>
    <?=js_link('bootstrap.min', true)?>

    <?php if (isset($js_after) && !empty($js_after)): ?>
        <?php foreach ($js_after as $js): ?>
            <?=$js?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($popper_js) && $popper_js): ?>
        <script>
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })
        </script>
    <?php endif; ?>

</body>
</html>
