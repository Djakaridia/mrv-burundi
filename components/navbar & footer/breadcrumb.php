<div class="row g-3 flex-between-end pb-2 mt-n4">
    <div class="col-auto mt-2">
        <?php
        if (isset($breadcrumbs) && is_array($breadcrumbs)) {
            foreach ($breadcrumbs as $breadcrumb) {
                if ($breadcrumb['active']) {
                    echo '<h4 class="page-title">' . $breadcrumb['title'] . '</h4>';
                    break;
                }
            }
        }
        ?>
    </div>

    <nav class="col-auto mt-0" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <?php
            if (isset($breadcrumbs) && is_array($breadcrumbs)) {
                foreach ($breadcrumbs as $breadcrumb) {
                    if ($breadcrumb['active']) {
                        echo '<li class="breadcrumb-item active" aria-current="page">' . $breadcrumb['title'] . '</li>';
                    } else {
                        echo '<li class="breadcrumb-item"><a href="' . $breadcrumb['url'] . '">' . $breadcrumb['title'] . '</a></li>';
                    }
                }
            }
            ?>
        </ol>
    </nav>
</div>