<?php
// inc/components.php

function render_star_rating($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $stars = str_repeat('★', $full) . str_repeat('☆', $empty + $half);
    return "<span style='color:#fbbf24;font-size:1.1em;'>$stars</span>";
}

function user_avatar($avatar_url, $size = 48) {
    $default = "https://randomuser.me/api/portraits/lego/1.jpg";
    $src = $avatar_url ?: $default;
    return "<img src='$src' alt='Avatar' style='width:{$size}px;height:{$size}px;border-radius:50%;object-fit:cover;'>";
}
?>