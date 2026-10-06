<?php
/**
 * Модальное окно подтверждения удаления сотрудника (jQuery UI dialog).
 *
 * Вместе с сотрудником модель удаляет все его идентификаторы (CARD),
 * запись с ID_PEP = 1 не удаляется (см. Model_Mancard_People::deletePerson()).
 *
 * Оформление — static/css/jquery-ui.css + views/mancard/modal_common.php.
 * Открывается глобальной функцией openDeletePersonDialog(personId, orgId, personName).
 */
?>
<style>
#delete-person-text {
    margin-bottom: 10px;
    font-size: 14px;
    line-height: 1.5;
}
#delete-person-warning {
    padding: 8px 10px;
    font-size: 13px;
    color: #8a6d3b;
    background: #fcf8e3;
    border: 1px solid #faebcc;
    border-radius: 4px;
}
</style>

<div id="delete-person-dialog" title="<?php echo __('Удаление сотрудника'); ?>" style="display: none;">
    <div id="delete-person-alert" class="ep-alert" style="display: none;">
        <span class="ui-icon ui-icon-alert ep-alert-icon"></span>
        <span id="delete-person-alert-text"></span>
    </div>

    <div id="delete-person-text"></div>

    <div id="delete-person-warning">
        <span class="glyphicon glyphicon-warning-sign"></span>
        <?php echo __('Вместе с сотрудником будут удалены все его идентификаторы (карты). Действие нельзя отменить.'); ?>
    </div>
</div>

<script>
$(function () {
    // См. комментарий в edit_person.php: Bootstrap перекрывает $.fn.button из jQuery UI.
    if ($.fn.button && typeof $.fn.button.noConflict === 'function') {
        $.fn.button.noConflict();
    }

    var urlDeletePerson = '<?php echo URL::site('mancard/delete_person'); ?>';

    var textIntro    = '<?php echo __('Вы уверены, что хотите удалить сотрудника'); ?>';
    var textDelete   = '<?php echo __('Удалить'); ?>';
    var textDeleting = '<?php echo __('Удаление...'); ?>';
    var textError    = '<?php echo __('Ошибка при выполнении операции'); ?>';

    var $dialog    = $('#delete-person-dialog');
    var $text      = $('#delete-person-text');
    var $alert     = $('#delete-person-alert');
    var $alertText = $('#delete-person-alert-text');
    var $deleteButton = null;

    var deleteId = 0;
    var deleteOrgId = 0;
    var isDeleting = false;

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showAlert(text) {
        $alert.addClass('ep-alert-error').show();
        $alertText.text(text || '');
    }

    function hideAlert() {
        $alert.removeClass('ep-alert-error ep-alert-success').hide();
        $alertText.text('');
    }

    function setDeleteBusy(busy) {
        if (!$deleteButton || !$deleteButton.length) {
            return;
        }

        $deleteButton
            .prop('disabled', busy)
            .toggleClass('ui-state-disabled', busy)
            .text(busy ? textDeleting : textDelete);
    }

    // ===== Удаление =====
    function deletePerson() {
        if (isDeleting || deleteId <= 0) {
            return;
        }

        isDeleting = true;
        setDeleteBusy(true);

        $.ajax({
            url: urlDeletePerson + '/' + deleteId,
            type: 'POST',
            dataType: 'json'
        }).done(function (response) {
            if (!response || !response.success) {
                isDeleting = false;
                setDeleteBusy(false);
                showAlert((response && response.message) ? response.message : textError);
                return;
            }

            var personId = deleteId;
            var orgId = deleteOrgId;

            $dialog.dialog('close');

            if (typeof window.mancardRefreshAfterPersonDelete === 'function') {
                window.mancardRefreshAfterPersonDelete(personId, orgId);
                return;
            }

            window.location.reload();
        }).fail(function (xhr) {
            isDeleting = false;
            setDeleteBusy(false);
            showAlert(textError + (xhr && xhr.status ? ' (HTTP ' + xhr.status + ')' : ''));
        });
    }

    // ===== Инициализация окна =====
    $dialog.dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: true,
        closeOnEscape: true,
        closeText: '<?php echo __('Закрыть'); ?>',
        width: 460,
        maxHeight: 360,
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
                text: textDelete,
                class: 'ep-btn ep-btn-delete',
                click: function () {
                    deletePerson();
                }
            }
        ],
        create: function () {
            var $wrapper = $dialog.closest('.ui-dialog');

            $wrapper.addClass('mancard-dialog mancard-delete-dialog');
            $wrapper.css('z-index', 1200);

            $deleteButton = $wrapper.find('.ui-dialog-buttonpane .ep-btn-delete');
        },
        open: function () {
            $('body').data('ep-overflow', $('body').css('overflow')).css('overflow', 'hidden');
        },
        close: function () {
            $('body').css('overflow', $('body').data('ep-overflow') || '');
            $('body').removeData('ep-overflow');
            isDeleting = false;
            setDeleteBusy(false);
        }
    });

    /**
     * Открыть окно подтверждения удаления сотрудника.
     * @param int    personId  ID_PEP
     * @param int    orgId     организация сотрудника
     * @param string personName ФИО для текста подтверждения
     */
    window.openDeletePersonDialog = function (personId, orgId, personName) {
        deleteId = parseInt(personId, 10) || 0;
        deleteOrgId = parseInt(orgId, 10) || 1;
        isDeleting = false;

        hideAlert();

        if (deleteId <= 0) {
            $text.html(escapeHtml(textError));
        } else {
            $text.html(
                textIntro + ' <strong>' + escapeHtml(personName || '') + '</strong>' +
                ' <span class="text-muted">(ID_PEP: ' + deleteId + ')</span>?'
            );
        }

        $dialog.dialog('open');
        $dialog.dialog('option', 'height', 'auto');
    };
});
</script>
