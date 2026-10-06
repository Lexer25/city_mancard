<?php
/**
 * Общие стили модальных окон модуля (jQuery UI dialog).
 *
 * Файл подключается один раз в views/mancard/index.php перед окнами
 * edit_person.php и edit_org.php, поэтому общие правила живут здесь,
 * а не дублируются в каждом окне.
 *
 * Требуется static/css/jquery-ui.css (подключается в modules/basis/views/template.php).
 */
?>
<style>
/* ===== Анимация ожидания ===== */
@-webkit-keyframes ep-spin {
    from { -webkit-transform: rotate(0deg); }
    to   { -webkit-transform: rotate(360deg); }
}
@keyframes ep-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
.glyphicon-spin {
    -webkit-animation: ep-spin 1s infinite linear;
    animation: ep-spin 1s infinite linear;
}

/* ===== Окно: выше фиксированного меню Bootstrap (z-index 1030) и его слоя затемнения ===== */
.ui-dialog.mancard-dialog {
    z-index: 1200;
    font-family: inherit;
    font-size: 14px;
    padding: 0;
    border-radius: 4px;
    -webkit-box-shadow: 0 6px 24px rgba(0, 0, 0, .35);
    box-shadow: 0 6px 24px rgba(0, 0, 0, .35);
}
.ui-dialog.mancard-dialog .ui-dialog-titlebar {
    padding: 8px 12px;
    border-radius: 4px 4px 0 0;
}
.ui-dialog.mancard-dialog .ui-dialog-title {
    width: auto;
    max-width: 85%;
    font-size: 14px;
}
.ui-dialog.mancard-dialog .ui-dialog-content {
    padding: 12px 16px;
    background: #fff;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane {
    margin-top: 0;
    padding: 10px 16px;
    background: #f5f5f5;
    border-top: 1px solid #ddd;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button {
    padding: 6px 18px;
    font-size: 13px;
}
/* Основная кнопка — «Сохранить» */
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-save {
    background: #337ab7;
    border-color: #2e6da4;
    color: #fff;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-save:hover,
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-save:focus {
    background: #286090;
    border-color: #204d74;
    color: #fff;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-save.ui-state-disabled {
    opacity: .65;
}
/* Опасное действие — «Удалить» */
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-delete {
    background: #d9534f;
    border-color: #d43f3a;
    color: #fff;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-delete:hover,
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-delete:focus {
    background: #c9302c;
    border-color: #ac2925;
    color: #fff;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane .ui-button.ep-btn-delete.ui-state-disabled {
    opacity: .65;
}
/* Запасной вид кнопок, если jQuery UI не навесил класс ui-button */
.ui-dialog.mancard-dialog .ui-dialog-buttonpane button:not(.ui-button) {
    min-width: 90px;
    padding: 6px 18px;
    font-size: 13px;
    color: #454545;
    background: #f6f6f6;
    border: 1px solid #c5c5c5;
    border-radius: 3px;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane button.ep-btn-save:not(.ui-button) {
    color: #fff;
    background: #337ab7;
    border-color: #2e6da4;
}
.ui-dialog.mancard-dialog .ui-dialog-buttonpane button.ep-btn-delete:not(.ui-button) {
    color: #fff;
    background: #d9534f;
    border-color: #d43f3a;
}

/* ===== Содержимое окон ===== */
.ep-org-line {
    padding: 5px 8px;
    margin-bottom: 10px;
    font-size: 12px;
    color: #555;
    background: #f7f7f7;
    border: 1px solid #eee;
    border-radius: 3px;
}
.ep-loading {
    padding: 30px 0;
    text-align: center;
    color: #777;
}
.ep-alert {
    padding: 8px 10px;
    margin-bottom: 10px;
    font-size: 13px;
    border: 1px solid transparent;
    border-radius: 4px;
}
.ep-alert-icon {
    float: left;
    margin: 2px 7px 0 0;
}
.ep-alert-error {
    color: #a94442;
    background: #f2dede;
    border-color: #ebccd1;
}
.ep-alert-success {
    color: #3c763d;
    background: #dff0d8;
    border-color: #d6e9c6;
}
.ep-required {
    color: #d9534f;
}
.form-control.ep-invalid {
    border-color: #a94442;
    -webkit-box-shadow: 0 0 4px rgba(169, 68, 66, .55);
    box-shadow: 0 0 4px rgba(169, 68, 66, .55);
}
.ep-hidden-submit {
    display: none;
}
</style>
