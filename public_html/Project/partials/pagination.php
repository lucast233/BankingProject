<?php
if (!isset($page)) {
    $page = 1;
}
if (!isset($total_pages)) {
    $total_pages = 1;
} ?>
<style>
.pagination {text-align: center;}
  .page-item {
    list-style: none;
    display: inline-block;
    text-align: center;
    margin: 0;
    padding: 15px;
    display: inline;
  }
</style>
<ul class="pagination">
    <li class="page-item <?php if (($page - 1) < 1) echo 'disabled'; ?>">
        <a class="page-link" href="?<?php pagination_filter($page - 1); ?>">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
            <a class="page-link" href="?<?php pagination_filter($i); ?>">
                <?php se($i); ?></a>
        </li>
    <?php endfor; ?>
    <li class="page-item <?php if (($page + 1) > $total_pages) echo 'disabled'; ?>">
        <a class="page-link" href="?<?php pagination_filter($page + 1); ?>">Next</a>
    </li>
</ul>