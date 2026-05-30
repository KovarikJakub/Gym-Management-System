<?php

function outputPager($current_page, $total_pages)
{
    if ($total_pages <= 1) return;

    $query_params = $_GET;
    
    echo '<div class="pager" style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $query_params['page'] = $i;
        $url = '?' . http_build_query($query_params);
        if ($i == $current_page) {
            echo '<span class="pager-item active" style="font-weight: bold; padding: 5px 15px; background-color: var(--title-color); color: var(--bg-color); border-radius: 5px;">' . $i . '</span>';
        } else {
            echo '<a href="' . htmlspecialchars($url) . '" class="pager-item" style="padding: 5px 15px; border: 1px solid var(--boxes-color); border-radius: 5px;">' . $i . '</a>';
        }
    }
    echo '</div>';
}
