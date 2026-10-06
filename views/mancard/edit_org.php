<?php
/**
 * Модальное окно организации (jQuery UI dialog):
 * добавление подразделения и переименование организации.
 *
 * Оформление окна берётся из static/css/jquery-ui.css и views/mancard/modal_common.php.
 *
 * Открывается глобальной функцией openOrgDialog(mode, orgId, orgName):
 *   mode = 'add'    — добавить подразделение в организацию orgId;
 *   mode = 'rename' — переименовать организацию orgId (orgName — текущее название).
 */
?>
<style>
#edit-org-form label {
    margin-bottom: 3px;
    font-size: 12px;
    font-weight: normal;
    color: #555;
}
#edit-org-form .form-group {
    margin-bottom: 6px;
}
</style>

<div id="edit-org-dialog" title="<?php echo __('Новая организация'); ?>" style="display: none;">
    <form id="edit-org-form" autocomplete="off">
        <input type="hidden" name="id_org" id="edit-org-id" value="0">
        <input type="hidden" name="parent_id" id="edit-org-parent-id" value="1">
        <!-- Скрытая кнопка: Enter в поле отправляет форму -->
        <button type="submit" class="ep-hidden-submit" tabindex="-1" aria-hidden="true"></button>

        <div id="edit-org-alert" class="ep-alert" style="display: none;">
            <span class="ui-icon ui-icon-alert ep-alert-icon"></span>
            <span id="edit-org-alert-text"></span>
        </div>

        <div class="ep-org-line">
            <span class="glyphicon glyphicon-home"></span>
            <span id="edit-org-caption-label"><?php echo __('Родительская организация'); ?></span>:
            <strong id="edit-org-caption">[1]</strong>
        </div>

        <div class="form-group">
            <label for="edit-org-name">
                <?php echo __('Название организации'); ?> <span class="ep-required">*</span>
            </label>
            <input type="text" class="form-control" id="edit-org-name" name="name" maxlength="255" autofocus>
        </div>
    </form>
</div>

<script>
$(function () {
    // См. комментарий в edit_person.php: Bootstrap перекрывает $.fn.button из jQuery UI.
    // Вызов идемпотентный — второй раз условие уже не сработает.
    if ($.fn.button && typeof $.fn.button.noConflict === 'function') {
        $.fn.button.noConflict();
    }

    var urlAddOrg    = '<?php echo URL::site('mancard/add_organization'); ?>';
    var urlRenameOrg = '<?php echo URL::site('mancard/rename_organization'); ?>';

    var textTitleAdd     = '<?php echo __('Новая организация'); ?>';
    var textTitleRename  = '<?php echo __('Редактировать организацию'); ?>';
    var textParentLabel  = '<?php echo __('Родительская организация'); ?>';
    var textOrgLabel     = '<?php echo __('Организация'); ?>';
    var textNameRequired = '<?php echo __('Введите название организации'); ?>';
    var textSave         = '<?php echo __('Сохранить'); ?>';
    var textSaving       = '<?php echo __('Сохранение...'); ?>';
    var textError        = '<?php echo __('Ошибка при выполнении операции'); ?>';
    var textSuccess      = '<?php echo __('Операция выполнена успешно'); ?>';

    var $dialog    = $('#edit-org-dialog');
    var $form      = $('#edit-org-form');
    var $nameInput = $('#edit-org-name');
    var $alert     = $('#edit-org-alert');
    var $alertText = $('#edit-org-alert-text');
    var $saveButton = null;

    var currentMode = 'add';
    var isSaving = false;

    // ===== Сообщение внутри окна =====
    function showAlert(text, type) {
        $alert
            .removeClass('ep-alert-error ep-alert-success')
            .addClass(type === 'success' ? 'ep-alert-success' : 'ep-alert-error')
            .show();
        $alertText.text(text || '');
    }

    function hideAlert() {
        $alert.removeClass('ep-alert-error ep-alert-success').hide();
        $alertText.text('');
    }

    // ===== Состояние кнопки «Сохранить» =====
    function setSaveBusy(busy) {
        if (!$saveButton || !$saveButton.length) {
            return;
        }

        $saveButton
            .prop('disabled', busy)
            .toggleClass('ui-state-disabled', busy)
            .text(busy ? textSaving : textSave);
    }

    // ===== Подпись организации =====
    function orgNameFromTree(orgId) {
        var $node = $('.tree-node[data-org-id="' + orgId + '"]').first();

        if (!$node.length) {
            return '';
        }

        return $.trim($node.find('.org-name').first().text());
    }

    function updateCaption(orgId, orgName) {
        $('#edit-org-caption').text('[' + orgId + ']' + (orgName ? ' ' + orgName : ''));
    }

    // ===== Сохранение =====
    function saveOrg() {
        if (isSaving) {
            return;
        }

        var name = $.trim($nameInput.val());

        if (name === '') {
            $nameInput.addClass('ep-invalid').trigger('focus');
            showAlert(textNameRequired);
            return;
        }

        var postData;
        var url;

        if (currentMode === 'rename') {
            url = urlRenameOrg;
            postData = {id: $('#edit-org-id').val(), name: name};
        } else {
            url = urlAddOrg;
            postData = {name: name, parent_id: $('#edit-org-parent-id').val()};
        }

        isSaving = true;
        setSaveBusy(true);

        $.ajax({
            url: url,
            type: 'POST',
            data: postData,
            dataType: 'json'
        }).done(function (response) {
            if (!response || !response.success) {
                isSaving = false;
                setSaveBusy(false);
                showAlert((response && response.message) ? response.message : textError);
                return;
            }

            var savedId  = parseInt($('#edit-org-id').val(), 10) || 0;
            var parentId = parseInt($('#edit-org-parent-id').val(), 10) || 1;

            if (savedId <= 0) {
                savedId = parseInt(response.id, 10) || 0;
            }

            showAlert(response.message || textSuccess, 'success');

            window.setTimeout(function () {
                $dialog.dialog('close');
                refreshAfterSave(savedId, parentId, name);
            }, 500);
        }).fail(function (xhr) {
            isSaving = false;
            setSaveBusy(false);
            showAlert(textError + (xhr && xhr.status ? ' (HTTP ' + xhr.status + ')' : ''));
        });
    }

    // ===== Обновление дерева и панели «Свойства» =====
    function refreshAfterSave(orgId, parentId, name) {
        if (currentMode === 'rename') {
            if (typeof window.mancardRefreshAfterOrgRename === 'function') {
                window.mancardRefreshAfterOrgRename(orgId, name);
                return;
            }
        } else if (typeof window.mancardRefreshAfterOrgAdd === 'function') {
            window.mancardRefreshAfterOrgAdd(orgId, parentId);
            return;
        }

        window.location.reload();
    }

    // ===== Инициализация окна =====
    $dialog.dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: true,
        closeOnEscape: true,
        closeText: '<?php echo __('Закрыть'); ?>',
        width: 480,
        maxHeight: 400,
        minWidth: 300,
        position: { my: 'center', at: 'center', of: window, collision: 'fit fit' },
        buttons: [
            {
                text: '<?php echo __('Отмена'); ?>',
                class: 'ep-btn ep-btn-cancel',
                click: function () {
                    $dialog.dialog('close');
                }
            },
            {
                text: textSave,
                class: 'ep-btn ep-btn-save',
                click: function () {
                    saveOrg();
                }
            }
        ],
        create: function () {
            var $wrapper = $dialog.closest('.ui-dialog');

            $wrapper.addClass('mancard-dialog mancard-org-dialog');
            $wrapper.css('z-index', 1200);

            $saveButton = $wrapper.find('.ui-dialog-buttonpane .ep-btn-save');
        },
        open: function () {
            $('body').data('ep-overflow', $('body').css('overflow')).css('overflow', 'hidden');
        },
        close: function () {
            $('body').css('overflow', $('body').data('ep-overflow') || '');
            $('body').removeData('ep-overflow');
            isSaving = false;
            setSaveBusy(false);
        }
    });

    /**
     * Открыть окно организации.
     * @param string mode   'add' — добавить подразделение, 'rename' — переименовать
     * @param int    orgId  для 'add' — родительская организация, для 'rename' — сама организация
     * @param string orgName текущее название (для 'rename')
     */
    window.openOrgDialog = function (mode, orgId, orgName) {
        currentMode = (mode === 'rename') ? 'rename' : 'add';
        orgId = parseInt(orgId, 10) || 1;
        orgName = $.trim(orgName || '');

        isSaving = false;
        hideAlert();
        $nameInput.removeClass('ep-invalid').val(currentMode === 'rename' ? orgName : '');

        if (currentMode === 'rename') {
            $dialog.dialog('option', 'title', textTitleRename);
            $('#edit-org-caption-label').text(textOrgLabel);
            $('#edit-org-id').val(orgId);
            $('#edit-org-parent-id').val(0);
            updateCaption(orgId, orgName || orgNameFromTree(orgId));
        } else {
            $dialog.dialog('option', 'title', textTitleAdd);
            $('#edit-org-caption-label').text(textParentLabel);
            $('#edit-org-id').val(0);
            $('#edit-org-parent-id').val(orgId);
            updateCaption(orgId, orgNameFromTree(orgId));
        }

        $dialog.dialog('open');
        $dialog.dialog('option', 'height', 'auto');

        // В режиме переименования удобно сразу заменить название целиком
        $nameInput.trigger('focus');

        if (currentMode === 'rename' && orgName !== '') {
            $nameInput.select();
        }
    };

    // Enter в поле = «Сохранить»
    $form.on('submit', function (event) {
        event.preventDefault();
        saveOrg();
    });

    // Снимаем подсветку поля при вводе
    $nameInput.on('input change', function () {
        $(this).removeClass('ep-invalid');
    });
});
</script>
