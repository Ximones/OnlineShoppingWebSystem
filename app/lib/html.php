<?php

function encode($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function html_text(string $key, array $attr = []): void {
    $value = encode($GLOBALS[$key] ?? '');
    $attrString = html_attr($attr);
    echo "<input type='text' id='$key' name='$key' value='$value' $attrString>";
}

function html_email(string $key, array $attr = []): void {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='email' id='$key' name='$key' value='$value' " . html_attr($attr) . '>';
}

function html_password(string $key, array $attr = []): void {
    echo "<input type='password' id='$key' name='$key' " . html_attr($attr) . '>';
}

function html_select(string $key, array $items, string $default = '- Select -', array $attr = []): void {
    $value = $GLOBALS[$key] ?? '';
    echo "<select id='$key' name='$key' " . html_attr($attr) . '>';
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $selected = $id == $value ? 'selected' : '';
        echo "<option value='$id' $selected>$text</option>";
    }
    echo '</select>';
}

function html_radios(string $key, array $items, array $attr = []): void {
    $value = $GLOBALS[$key] ?? '';
    foreach ($items as $id => $text) {
        $checked = $id == $value ? 'checked' : '';
        echo "<label class='radio-inline'><input type='radio' name='$key' value='$id' $checked " . html_attr($attr) . ">$text</label>";
    }
}

function html_attr(array $attr = []): string {
    $buffer = [];
    foreach ($attr as $key => $value) {
        $buffer[] = $key . "='" . encode($value) . "'";
    }
    return implode(' ', $buffer);
}

function err(string $key): void {
    global $_err;
    if (!empty($_err[$key])) {
        echo "<span class='err'>{$_err[$key]}</span>";
    } else {
        echo '<span class="err-placeholder"></span>';
    }
}


