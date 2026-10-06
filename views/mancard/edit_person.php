<?php
/**
 * Модальное окно добавления/редактирования сотрудника.
 *
 * Окно построено на jQuery UI dialog: разметка + скрипт здесь, оформление окна
 * (заголовок, слой затемнения, кнопки) берётся из static/css/jquery-ui.css,
 * которое подключается в modules/basis/views/template.php.
 *
 * Открывается глобальной функцией openEditPersonDialog(personId, orgId):
 *   personId = 0  — добавление нового сотрудника в организацию orgId;
 *   personId > 0 — редактирование существующего сотрудника.
 */
$personFieldsets = array(
    array(
        'legend' => __('Личные данные'),
        'fields' => array(
            array('name' => 'surname',    'label' => __('Фамилия'),        'required' => true, 'col' => 4),
            array('name' => 'name',       'label' => __('Имя'),            'required' => true, 'col' => 4),
            array('name' => 'patronymic', 'label' => __('Отчество'),       'col' => 4),
            array('name' => 'datebirth',  'label' => __('Дата рождения'),  'type' => 'date', 'col' => 4),
            array('name' => 'placebirth', 'label' => __('Место рождения'), 'col' => 8),
        ),
    ),
    array(
        'legend' => __('Контакты'),
        'fields' => array(
            array('name' => 'phonework',     'label' => __('Телефон'),           'col' => 4),
            array('name' => 'phonecellular', 'label' => __('Мобильный телефон'), 'col' => 4),
            array('name' => 'phonehome',     'label' => __('Домашний телефон'),  'col' => 4),
            array('name' => 'placelife',     'label' => __('Адрес проживания'),  'col' => 6),
            array('name' => 'placereg',      'label' => __('Адрес регистрации'), 'col' => 6),
        ),
    ),
    array(
        'legend' => __('Документы'),
        'fields' => array(
            array('name' => 'numdoc',   'label' => __('Паспорт'),     'col' => 4),
            array('name' => 'datedoc',  'label' => __('Дата выдачи'), 'type' => 'date', 'col' => 4),
            array('name' => 'placedoc', 'label' => __('Кем выдан'),   'col' => 4),
        ),
    ),
    array(
        'legend' => __('Служебная информация'),
        'fields' => array(
            array('name' => 'post',    'label' => __('Должность'),            'col' => 4),
            array('name' => 'tabnum',  'label' => __('Табельный номер'),      'col' => 4),
            array('name' => 'login',   'label' => __('Логин'),                'col' => 4),
            array(
                'name'    => 'active',
                'label'   => __('Статус'),
                'type'    => 'select',
                'col'     => 4,
                'options' => array('1' => __('Активен'), '0' => __('Неактивен')),
            ),
            array('name' => 'note',    'label' => __('Примечание'),       'col' => 8),
            array('name' => 'sysnote', 'label' => __('Служебные записи'), 'type' => 'textarea', 'rows' => 3, 'col' => 12),
        ),
    ),
);
?>
<style>
/* Общие стили модальных окон (окно, кнопки, сообщения) — в views/mancard/modal_common.php,
   он подключается в index.php перед этим окном. Здесь только сетка и поля формы сотрудника. */

/* Сетка формы для полей сотрудника */
#edit-person-form .ep-fieldset {
    margin: 0 0 12px 0;
    padding: 8px 12px 0 12px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}
#edit-person-form .ep-fieldset legend {
    display: block;
    width: auto;
    margin: 0 0 4px 0;
    padding: 0 6px;
    font-size: 13px;
    font-weight: bold;
    line-height: 1.4;
    color: #337ab7;
    border: 0;
}
#edit-person-form .ep-row {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin: 0 -6px;
}
#edit-person-form .ep-col {
    padding: 0 6px;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}
#edit-person-form .ep-col-4  { width: 33.3333%; }
#edit-person-form .ep-col-6  { width: 50%; }
#edit-person-form .ep-col-8  { width: 66.6666%; }
#edit-person-form .ep-col-12 { width: 100%; }
#edit-person-form .form-group {
    margin-bottom: 12px;
}
#edit-person-form label {
    margin-bottom: 3px;
    font-size: 12px;
    font-weight: normal;
    color: #555;
}
@media (max-width: 720px) {
    #edit-person-form .ep-col-4,
    #edit-person-form .ep-col-6,
    #edit-person-form .ep-col-8 {
        width: 100%;
    }
}
</style>

<div id="edit-person-dialog" title="<?php echo __('Новый сотрудник'); ?>" style="display: none;">
    <form id="edit-person-form" autocomplete="off">
        <input type="hidden" name="id_pep" id="edit-id-pep" value="0">
        <input type="hidden" name="id_org" id="edit-id-org" value="1">
        <!-- Скрытая кнопка: Enter в любом поле отправляет форму -->
        <button type="submit" class="ep-hidden-submit" tabindex="-1" aria-hidden="true"></button>

        <div class="ep-org-line">
            <span class="glyphicon glyphicon-home"></span>
            <?php echo __('Организация'); ?>:
            <strong id="edit-org-caption">[1]</strong>
        </div>

        <div id="edit-person-alert" class="ep-alert" style="display: none;">
            <span class="ui-icon ui-icon-alert ep-alert-icon"></span>
            <span id="edit-person-alert-text"></span>
        </div>

        <div id="edit-person-loading" class="ep-loading" style="display: none;">
            <span class="glyphicon glyphicon-refresh glyphicon-spin"></span>
            <?php echo __('Загрузка...'); ?>
        </div>

        <?php foreach ($personFieldsets as $fieldset): ?>
            <fieldset class="ep-fieldset">
                <legend><?php echo $fieldset['legend']; ?></legend>
                <div class="ep-row">
                    <?php foreach ($fieldset['fields'] as $field): ?>
                        <?php
                        $fieldName = $field['name'];
                        $fieldId   = 'edit-' . $fieldName;
                        $fieldType = isset($field['type']) ? $field['type'] : 'text';
                        $fieldCol  = isset($field['col']) ? (int) $field['col'] : 12;
                        $fieldReq  = !empty($field['required']);
                        ?>
                        <div class="ep-col ep-col-<?php echo $fieldCol; ?>">
                            <div class="form-group">
                                <label for="<?php echo $fieldId; ?>">
                                    <?php echo $field['label']; ?><?php if ($fieldReq): ?> <span class="ep-required">*</span><?php endif; ?>
                                </label>
                                <?php if ($fieldType === 'textarea'): ?>
                                    <textarea class="form-control" id="<?php echo $fieldId; ?>" name="<?php echo $fieldName; ?>"
                                              rows="<?php echo isset($field['rows']) ? (int) $field['rows'] : 3; ?>"></textarea>
                                <?php elseif ($fieldType === 'select'): ?>
                                    <select class="form-control" id="<?php echo $fieldId; ?>" name="<?php echo $fieldName; ?>">
                                        <?php foreach ($field['options'] as $optionValue => $optionLabel): ?>
                                            <option value="<?php echo $optionValue; ?>"><?php echo $optionLabel; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="<?php echo $fieldType; ?>" class="form-control" id="<?php echo $fieldId; ?>"
                                           name="<?php echo $fieldName; ?>"<?php if ($fieldName === 'surname'): ?> autofocus<?php endif; ?>>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endforeach; ?>
    </form>
</div>

<script>
$(function () {
    // Bootstrap подключается после jQuery UI и перекрывает $.fn.button, из-за чего
    // jQuery UI не может отрисовать кнопки окна (нет классов ui-button и иконки закрытия).
    // Bootstrap-овский data-api ([data-toggle="button"]) работает через собственную ссылку
    // на плагин, а прямых вызовов $(...).button() в проекте нет — возвращаем метод jQuery UI.
    if ($.fn.button && typeof $.fn.button.noConflict === 'function') {
        $.fn.button.noConflict();
    }

    // Текстовые поля сотрудника: ключ = имя поля формы, значение берётся из ответа get_person
    var PERSON_TEXT_FIELDS = [
        'surname', 'name', 'patronymic', 'tabnum', 'login', 'post',
        'phonework', 'phonecellular', 'phonehome',
        'placebirth', 'placelife', 'placereg',
        'numdoc', 'placedoc', 'note', 'sysnote'
    ];

    var urlGetPerson    = '<?php echo URL::site('mancard/get_person'); ?>';
    var urlAddPerson    = '<?php echo URL::site('mancard/add_person'); ?>';
    var urlUpdatePerson = '<?php echo URL::site('mancard/update_person'); ?>';

    var textTitleNew    = '<?php echo __('Новый сотрудник'); ?>';
    var textTitleEdit   = '<?php echo __('Редактировать сотрудника'); ?>';
    var textSave        = '<?php echo __('Сохранить'); ?>';
    var textSaving      = '<?php echo __('Сохранение...'); ?>';
    var textRequired    = '<?php echo __('Заполните обязательные поля'); ?>';
    var textError       = '<?php echo __('Ошибка при выполнении операции'); ?>';
    var textLoadError   = '<?php echo __('Ошибка загрузки'); ?>';
    var textSuccess     = '<?php echo __('Операция выполнена успешно'); ?>';

    var $dialog      = $('#edit-person-dialog');
    var $form        = $('#edit-person-form');
    var $alert       = $('#edit-person-alert');
    var $alertText   = $('#edit-person-alert-text');
    var $loading     = $('#edit-person-loading');
    var $orgCaption  = $('#edit-org-caption');
    var $saveButton  = null;
    // Защита от «опоздавшего» ответа get_person, если окно уже открыли для другого сотрудника
    var requestToken = 0;
    // Защита от повторной отправки формы
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

    function updateOrgCaption(orgId, orgName) {
        $orgCaption.text('[' + orgId + ']' + (orgName ? ' ' + orgName : ''));
    }

    // ===== Дата: Firebird отдаёт YYYY-MM-DD (иногда с временем), поддерживаем и DD.MM.YYYY =====
    function normalizeDate(value) {
        if (!value) {
            return '';
        }

        var str = String(value);
        var iso = str.match(/^(\d{4})-(\d{2})-(\d{2})/);

        if (iso) {
            return iso[0];
        }

        var ru = str.match(/^(\d{2})\.(\d{2})\.(\d{4})/);

        if (ru) {
            return ru[3] + '-' + ru[2] + '-' + ru[1];
        }

        return '';
    }

    // ===== Форма: очистка и заполнение =====
    function resetPersonForm(orgId) {
        $form[0].reset();
        $('#edit-id-pep').val(0);
        $('#edit-id-org').val(orgId);
        $('#edit-active').val('1');
        $form.find('.ep-invalid').removeClass('ep-invalid');
        hideAlert();
        updateOrgCaption(orgId, orgNameFromTree(orgId));
    }

    function fillPersonForm(data) {
        $.each(PERSON_TEXT_FIELDS, function (index, field) {
            var value = data[field.toUpperCase()];
            $('#edit-' + field).val(value === null || value === undefined ? '' : value);
        });

        $('#edit-datebirth').val(normalizeDate(data.DATEBIRTH));
        $('#edit-datedoc').val(normalizeDate(data.DATEDOC));
        $('#edit-active').val(String(data.ACTIVE) === '0' ? '0' : '1');
        $('#edit-id-pep').val(data.ID_PEP);
        $('#edit-id-org').val(data.ID_ORG);

        $form.find('.ep-invalid').removeClass('ep-invalid');
        updateOrgCaption(data.ID_ORG, data.ORG_NAME || orgNameFromTree(data.ID_ORG));
    }

    function focusFirstField() {
        var $first = $('#edit-surname');

        if ($first.length && $first.is(':visible')) {
            $first.trigger('focus');
        }
    }

    // ===== Проверка обязательных полей =====
    function validatePersonForm() {
        var missing = [];

        $form.find('.ep-invalid').removeClass('ep-invalid');

        $.each(['surname', 'name'], function (index, field) {
            var $input = $('#edit-' + field);

            if ($.trim($input.val()) === '') {
                $input.addClass('ep-invalid');
                missing.push($.trim($form.find('label[for="edit-' + field + '"]').text().replace('*', '')));
            }
        });

        if (missing.length > 0) {
            showAlert(textRequired + ': ' + missing.join(', '));
            $form.find('.ep-invalid').first().trigger('focus');
            return false;
        }

        hideAlert();
        return true;
    }

    // ===== Сохранение =====
    function savePerson() {
        if (isSaving || !validatePersonForm()) {
            return;
        }

        var personId = parseInt($('#edit-id-pep').val(), 10) || 0;
        var orgId    = parseInt($('#edit-id-org').val(), 10) || 1;
        var postData = {};

        $.each($form.serializeArray(), function (index, field) {
            postData[field.name] = field.value;
        });

        isSaving = true;
        setSaveBusy(true);

        $.ajax({
            url: personId > 0 ? urlUpdatePerson : urlAddPerson,
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

            var savedId = personId > 0 ? personId : (parseInt(response.id, 10) || 0);

            // Показываем результат и закрываем окно
            showAlert(response.message || textSuccess, 'success');

            window.setTimeout(function () {
                $dialog.dialog('close');
                refreshAfterPersonSave(savedId, orgId);
            }, 600);
        }).fail(function (xhr) {
            isSaving = false;
            setSaveBusy(false);
            showAlert(textError + (xhr && xhr.status ? ' (HTTP ' + xhr.status + ')' : ''));
        });
    }

    // ===== Обновление дерева и панели «Свойства» после сохранения =====
    function refreshAfterPersonSave(personId, orgId) {
        if (typeof window.mancardRefreshAfterPersonSave === 'function') {
            window.mancardRefreshAfterPersonSave(personId, orgId);
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
        width: 880,
        maxHeight: 640,
        minWidth: 320,
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
                    savePerson();
                }
            }
        ],
        create: function () {
            var $wrapper = $dialog.closest('.ui-dialog');

            $wrapper.addClass('mancard-dialog mancard-person-dialog');
            // jQuery UI ставит ui-front (z-index: 100) — поднимаем окно над меню Bootstrap
            $wrapper.css('z-index', 1200);

            $saveButton = $wrapper.find('.ui-dialog-buttonpane .ep-btn-save');
        },
        open: function () {
            // jQuery UI, в отличие от Bootstrap modal, не блокирует прокрутку страницы
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
     * Открыть окно сотрудника.
     * @param int personId 0 — новый сотрудник
     * @param int orgId    организация сотрудника
     */
    window.openEditPersonDialog = function (personId, orgId) {
        personId = parseInt(personId, 10) || 0;
        orgId    = parseInt(orgId, 10) || 1;

        var token = ++requestToken;

        isSaving = false;
        resetPersonForm(orgId);
        $dialog.dialog('option', 'title', personId > 0 ? textTitleEdit : textTitleNew);

        if (personId > 0) {
            $('#edit-id-pep').val(personId);
            $form.hide();
            $loading.show();
            $dialog.dialog('open');
            $dialog.dialog('option', 'height', 'auto');

            $.ajax({
                url: urlGetPerson + '/' + personId,
                type: 'GET',
                dataType: 'json'
            }).done(function (response) {
                if (token !== requestToken) {
                    return;
                }

                if (response && response.success && response.data) {
                    fillPersonForm(response.data);
                } else {
                    showAlert((response && response.message) ? response.message : textLoadError);
                }
            }).fail(function () {
                if (token !== requestToken) {
                    return;
                }

                showAlert(textLoadError);
            }).always(function () {
                if (token !== requestToken) {
                    return;
                }

                $loading.hide();
                $form.show();
                $dialog.dialog('option', 'height', 'auto');
                focusFirstField();
            });

            return;
        }

        $loading.hide();
        $form.show();
        $dialog.dialog('open');
        $dialog.dialog('option', 'height', 'auto');
        focusFirstField();
    };

    // Enter в любом поле формы = «Сохранить»
    $form.on('submit', function (event) {
        event.preventDefault();
        savePerson();
    });

    // Снимаем подсветку обязательного поля при вводе
    $form.on('input change', '.ep-invalid', function () {
        $(this).removeClass('ep-invalid');
    });
});
</script>
