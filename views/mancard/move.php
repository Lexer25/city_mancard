<style>
/* ... существующие стили ... */

/* Подсветка выбранной организации в правой панели */
.tree-item-selected {
    background-color: #d4edda !important;
    border-left: 4px solid #28a745 !important;
    border-radius: 3px !important;
    padding-left: 4px !important;
}

.tree-item-selected .item-name {
    font-weight: bold !important;
    color: #155724 !important;
}

/* Анимация подсветки */
.tree-item-org {
    transition: all 0.2s ease;
}

.tree-item-org:hover {
    background-color: #f0f0f0;
}
</style>
<?php
/**
 * Страница массового перемещения сотрудников и организаций
 * Левая панель - Источник (с чекбоксами)
 * Правая панель - Назначение (выбор кликом)
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-transfer"></span>
            <?php echo __('Массовое перемещение'); ?>
            <div class="pull-right">
                <button class="btn btn-xs btn-default" id="btn-switch-view">
                    <span class="glyphicon glyphicon-th-list"></span> <?php echo __('Переключить вид'); ?>
                </button>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        
        <!-- Информационная панель -->
        <div class="alert alert-info">
            <span class="glyphicon glyphicon-info-sign"></span>
            <?php echo __('Выберите элементы в левой панели (сотрудников или организаций), затем кликните на организацию в правой панели для выбора цели и нажмите стрелку для перемещения.'); ?>
        </div>
        
        <div class="row move-container">
            <!-- Левая панель - Источник (с чекбоксами) -->
            <div class="col-md-5">
                <div class="panel panel-default move-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <span class="glyphicon glyphicon-arrow-right"></span>
                            <?php echo __('Источник'); ?>
                            <span class="pull-right">
                                <button class="btn btn-xs btn-success" id="btn-select-all" title="<?php echo __('Выбрать все'); ?>">
                                    <span class="glyphicon glyphicon-check"></span>
                                </button>
                                <button class="btn btn-xs btn-default" id="btn-deselect-all" title="<?php echo __('Снять выделение'); ?>">
                                    <span class="glyphicon glyphicon-unchecked"></span>
                                </button>
                                <button class="btn btn-xs btn-info" id="btn-expand-left" title="<?php echo __('Развернуть всё'); ?>">
                                    <span class="glyphicon glyphicon-plus-sign"></span>
                                </button>
                                <button class="btn btn-xs btn-warning" id="btn-collapse-left" title="<?php echo __('Свернуть всё'); ?>">
                                    <span class="glyphicon glyphicon-minus-sign"></span>
                                </button>
                            </span>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <!-- Поиск -->
                        <div class="form-group" style="margin-bottom: 5px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                                <input type="text" class="form-control" id="left-search" placeholder="<?php echo __('Поиск по названию или ФИО...'); ?>">
                            </div>
                        </div>
                        
                        <!-- Дерево -->
                        <div class="move-list" id="left-tree">
                            <ul class="file-tree" id="left-tree-root">
                                <?php 
                                $rootOrg = null;
                                foreach ($org_tree as $org) {
                                    if ($org['ID_ORG'] == 1) {
                                        $rootOrg = $org;
                                        break;
                                    }
                                }
                                
                                if ($rootOrg): 
                                ?>
                                    <?php echo View::factory('mancard/move_from_tree_node_with_cards', array(
                                        'org' => $rootOrg,
                                        'level' => 0
                                    )); ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="panel-footer" style="padding: 5px 10px;">
                            <small class="text-muted">
                                <span class="glyphicon glyphicon-tag"></span>
                                <?php echo __('Выбрано'); ?>: <span id="selected-count">0</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Центр - кнопки перемещения -->
            <div class="col-md-2 text-center move-buttons">
                <div style="display: flex; flex-direction: column; justify-content: center; height: 100%; padding: 20px 0;">
                    <button class="btn btn-primary btn-lg" id="btn-move" style="margin-bottom: 20px;" title="<?php echo __('Переместить выбранное вправо'); ?>">
                        <span class="glyphicon glyphicon-arrow-right" style="font-size: 2em;"></span>
                        <br>
                        <small><?php echo __('Переместить →'); ?></small>
                    </button>
                    
                    <!-- Информация о выбранной цели -->
                    <div id="target-info">
                        <strong><?php echo __('Цель'); ?>:</strong>
                        <span id="target-name" class="text-muted"><?php echo __('не выбрана'); ?></span>
                        <br>
                        <small class="text-muted">ID: <span id="target-id">—</span></small>
                    </div>
                </div>
            </div>
            
            <!-- Правая панель - Назначение (без чекбоксов, выбор кликом) -->
            <div class="col-md-5">
                <div class="panel panel-success move-panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <span class="glyphicon glyphicon-arrow-left"></span>
                            <?php echo __('Назначение'); ?>
                            <span class="pull-right">
                                <button class="btn btn-xs btn-info" id="btn-expand-right" title="<?php echo __('Развернуть всё'); ?>">
                                    <span class="glyphicon glyphicon-plus-sign"></span>
                                </button>
                                <button class="btn btn-xs btn-warning" id="btn-collapse-right" title="<?php echo __('Свернуть всё'); ?>">
                                    <span class="glyphicon glyphicon-minus-sign"></span>
                                </button>
                                <button class="btn btn-xs btn-default" id="btn-refresh" title="<?php echo __('Обновить'); ?>">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                </button>
                            </span>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <!-- Поиск -->
                        <div class="form-group" style="margin-bottom: 5px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                                <input type="text" class="form-control" id="right-search" placeholder="<?php echo __('Поиск по названию...'); ?>">
                            </div>
                        </div>
                        
                        <!-- Дерево -->
                        <div class="move-list" id="right-tree">
                            <ul class="file-tree" id="right-tree-root">
                                <?php 
                                if ($rootOrg): 
                                ?>
                                    <?php echo View::factory('mancard/move_to_tree_node_with_cards', array(
                                        'org' => $rootOrg,
                                        'level' => 0
                                    )); ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="panel-footer" style="padding: 5px 10px;">
                            <small class="text-muted">
                                <span class="glyphicon glyphicon-info-sign"></span>
                                <?php echo __('Кликните на организацию для выбора цели'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
$(document).ready(function() {
    var targetOrgId = null;
    var targetOrgName = null;
    
    // ===== Получение класса для типа карты =====
    function getCardClass(cardType) {
        var typeMap = {
            'Карта EM-marine': 'card-badge-rfid',
            'RFID': 'card-badge-rfid',
            'FP': 'card-badge-fp',
            'Отпечаток пальца': 'card-badge-fp',
            'ШК': 'card-badge-barcode',
            'Штрих-код': 'card-badge-barcode',
            'BAR-code': 'card-badge-barcode',
            'ГРЗ': 'card-badge-grz',
            'grz': 'card-badge-grz',
            'FaceID': 'card-badge-faceid',
            'Распознавание лица': 'card-badge-faceid'
        };
        return typeMap[cardType] || 'card-badge-default';
    }
    
    function getCardIcon(cardType) {
        var iconMap = {
            'Карта EM-marine': 'glyphicon glyphicon-credit-card',
            'RFID': 'glyphicon glyphicon-credit-card',
            'FP': 'glyphicon glyphicon-hand-up',
            'Отпечаток пальца': 'glyphicon glyphicon-hand-up',
            'ШК': 'glyphicon glyphicon-barcode',
            'Штрих-код': 'glyphicon glyphicon-barcode',
            'BAR-code': 'glyphicon glyphicon-barcode',
            'ГРЗ': 'glyphicon glyphicon-road',
            'grz': 'glyphicon glyphicon-road',
            'FaceID': 'glyphicon glyphicon-user',
            'Распознавание лица': 'glyphicon glyphicon-user'
        };
        return iconMap[cardType] || 'glyphicon glyphicon-credit-card';
    }
    
    function renderPersonItem(person) {
        var fullName = person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC;
        var statusClass = person.ACTIVE == 1 ? '' : 'person-status-inactive';
        var statusColor = person.ACTIVE == 1 ? '' : 'text-muted';
        
        var cardsHtml = '';
        if (person.CARDS && person.CARDS.length > 0) {
            cardsHtml = '<span class="cards-container">';
            $.each(person.CARDS, function(idx, card) {
                var cardClass = getCardClass(card.CARDTYPE_NAME);
                var cardIcon = getCardIcon(card.CARDTYPE_NAME);
                var inactiveClass = card.ACTIVE == 1 ? '' : 'card-badge-inactive';
                var displayName = card.CARDTYPE_SMALLNAME || card.CARDTYPE_NAME;
                
                cardsHtml += '<span class="card-badge ' + cardClass + ' ' + inactiveClass + '" title="' + 
                    card.CARDTYPE_NAME + (card.ACTIVE == 0 ? ' (неактивна)' : '') + '">' +
                    '<span class="' + cardIcon + '"></span> ' +
                    displayName + ': ' + card.ID_CARD +
                    '</span>';
            });
            cardsHtml += '</span>';
        } else {
            cardsHtml = '<span class="text-muted" style="font-size: 10px; margin-left: 5px;">' +
                '<span class="glyphicon glyphicon-info-sign"></span> нет идентификаторов</span>';
        }
        
        // Для левой панели - с чекбоксом, для правой - без
        var isLeft = $(this).closest('#left-tree').length > 0;
        var checkboxHtml = isLeft ? '<input type="checkbox" class="item-checkbox" value="person_' + person.ID_PEP + '" data-type="person" data-id="' + person.ID_PEP + '" data-name="' + fullName + '">' : '';
        
        return '<div class="tree-item tree-item-person ' + statusClass + '" data-person-id="' + person.ID_PEP + '" data-org-id="' + person.ID_ORG + '">' +
            checkboxHtml +
            '<span class="tree-toggle">' +
            '<span class="glyphicon glyphicon-user ' + statusColor + '"></span>' +
            '</span>' +
            '<span class="item-name person-name ' + statusColor + '">' + fullName + '</span>' +
            (person.POST ? ' <span class="person-post">(' + person.POST + ')</span>' : '') +
            cardsHtml +
            '</div>';
    }
    
    // ===== Переключение узла =====
    $(document).on('click', '.tree-toggle', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.tree-node');
        var $children = $node.children('.tree-children');
        var $icon = $(this).find('.glyphicon');
        
        if ($node.data('type') === 'org') {
            if ($children.is(':visible')) {
                $children.slideUp();
                $icon.removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');
                $node.data('expanded', false);
            } else {
                if ($children.children().length === 0) {
                    loadNodeContent($node);
                } else {
                    $children.slideDown();
                    $icon.removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
                    $node.data('expanded', true);
                }
            }
        }
    });
    
    // ===== Загрузка содержимого узла =====
    function loadNodeContent($node) {
        var orgId = $node.data('org-id');
        var $children = $node.children('.tree-children');
        var $icon = $node.find('.tree-toggle .glyphicon');
        var side = $node.closest('.move-list').attr('id') === 'left-tree' ? 'left' : 'right';
        
        $.ajax({
            url: '<?php echo URL::site('mancard/get_org_structure_cards'); ?>/' + orgId,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $children.html('<div class="text-center text-muted" style="padding: 10px;"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> <?php echo __('Загрузка...'); ?></div>');
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderNodeChildren($children, response.data, side);
                    $children.slideDown();
                    $icon.removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
                    $node.data('expanded', true);
                } else {
                    $children.html('<div class="text-muted" style="padding: 10px;"><span class="glyphicon glyphicon-info-sign"></span> <?php echo __('Нет данных'); ?></div>');
                }
            },
            error: function() {
                $children.html('<div class="text-danger" style="padding: 10px;"><span class="glyphicon glyphicon-exclamation-sign"></span> <?php echo __('Ошибка загрузки'); ?></div>');
            }
        });
    }
    
// ===== Рендеринг детей узла =====
function renderNodeChildren($container, data, side) {
    $container.empty();
    var isLeft = side === 'left';
    
    // Рендерим организации
    if (data.CHILDREN && data.CHILDREN.length > 0) {
        $.each(data.CHILDREN, function(index, child) {
            var peopleCount = child.PEOPLE_COUNT || 0;
            var childrenCount = child.CHILDREN_COUNT || 0;
            var checkboxId = side + '_org_' + child.ID_ORG;
            
            var $li = $('<li class="tree-node" data-org-id="' + child.ID_ORG + '" data-type="org" data-expanded="false">');
            var $div = $('<div class="tree-item tree-item-org" data-org-id="' + child.ID_ORG + '" data-org-name="' + child.NAME + '">');
            
            // Чекбокс для левой панели (кроме корня)
            if (isLeft) {
                if (child.ID_ORG != 1) {
                    $div.append('<input type="checkbox" class="item-checkbox" id="' + checkboxId + '" ' +
                        'value="org_' + child.ID_ORG + '" ' +
                        'data-type="org" data-id="' + child.ID_ORG + '" data-name="' + child.NAME + '">');
                } else {
                    $div.append('<span style="width: 20px; flex-shrink: 0;"></span>');
                }
            }
            
            var $toggle = $('<span class="tree-toggle">');
            $toggle.append('<span style="font-size: 16px;">📁</span>');
            $div.append($toggle);
            
            // ID_ORG перед названием
            $div.append('<span class="org-id" style="color: #999; font-size: 11px; font-family: monospace; margin-left: 3px;">[' + child.ID_ORG + ']</span>');
            $div.append('<span class="item-name">' + child.NAME + '</span>');
            
            // Бейдж с количеством сотрудников
            var badgeClass = peopleCount > 0 ? 'badge' : 'badge badge-empty';
            var badgeStyle = peopleCount > 0 ? 'background-color: #337ab7;' : 'background-color: #f5f5f5; color: #ccc;';
            $div.append('<span class="' + badgeClass + '" style="margin-left: 5px; font-size: 10px; ' + badgeStyle + '">' +
                '👤 ' + peopleCount +
                '</span>');
            
            // Бейдж с количеством дочерних организаций
            if (childrenCount > 0) {
                $div.append('<span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de;">' +
                    '📁 ' + childrenCount +
                    '</span>');
            }
            
            $li.append($div);
            
            var $childrenUl = $('<ul class="tree-children" style="display:none;">');
            $li.append($childrenUl);
            
            $container.append($li);
        });
    }
    
    // ===== РЕНДЕРИМ СОТРУДНИКОВ С ID_PEP =====
    if (data.PEOPLE && data.PEOPLE.length > 0) {
        $.each(data.PEOPLE, function(index, person) {
            var $li = $('<li class="tree-node" data-person-id="' + person.ID_PEP + '" data-type="person">');
            var $div = $('<div class="tree-item tree-item-person" data-person-id="' + person.ID_PEP + '" data-org-id="' + person.ID_ORG + '">');
            
            // Чекбокс для сотрудников в левой панели
            if (isLeft) {
                $div.append('<input type="checkbox" class="item-checkbox" ' +
                    'value="person_' + person.ID_PEP + '" ' +
                    'data-type="person" data-id="' + person.ID_PEP + '" ' +
                    'data-name="' + person.SURNAME + ' ' + person.NAME + '">');
            }
            
            // Иконка пользователя
            var $toggle = $('<span class="tree-toggle">');
            $toggle.append('<span style="font-size: 16px;">👤</span>');
            $div.append($toggle);
            
            // ===== ID_PEP ПЕРЕД ИМЕНЕМ =====
            $div.append('<span class="person-id" style="color: #999; font-size: 11px; font-family: monospace; margin-left: 3px;">[' + person.ID_PEP + ']</span>');
            
            var fullName = person.SURNAME + ' ' + person.NAME + ' ' + person.PATRONYMIC;
            $div.append('<span class="item-name">' + fullName + '</span>');
            
            if (person.POST) {
                $div.append('<span class="person-post" style="color: #999; font-size: 11px; margin-left: 5px;">(' + person.POST + ')</span>');
            }
            
            $li.append($div);
            $container.append($li);
        });
    }
    
    // Если ничего нет
    if ($container.children().length === 0) {
        $container.html('<div class="text-muted" style="padding: 10px;"><span class="glyphicon glyphicon-info-sign"></span> Пусто</div>');
    }
}
    
    // ===== Выбор организации в правой панели =====
    $(document).on('click', '#right-tree .tree-item-org', function(e) {
        if ($(e.target).closest('.tree-toggle').length > 0) {
            return;
        }
        
        var orgId = $(this).data('org-id');
        var orgName = $(this).data('org-name');
        
        if (!orgId) return;
       /*  if (orgId == 1) {
            alert('<?php echo __('Нельзя выбрать корневую организацию как цель'); ?>');
            return;
        } */
        
        // Снимаем выделение со всех
        $('#right-tree .tree-item-org').removeClass('tree-item-selected');
        
        // Выделяем выбранную
        $(this).addClass('tree-item-selected');
        
        targetOrgId = orgId;
        targetOrgName = orgName;
        
        $('#target-name').text(orgName);
        $('#target-id').text(orgId);
    });
    
    // ===== Обновление счетчика выбранных =====
    function updateSelectedCount() {
        var count = $('#left-tree .item-checkbox:checked').length;
        $('#selected-count').text(count);
    }
    
    $(document).on('change', '#left-tree .item-checkbox', function() {
        updateSelectedCount();
    });
    
    // ===== Выбрать все =====
    $('#btn-select-all').on('click', function() {
        $('#left-tree .item-checkbox').prop('checked', true);
        updateSelectedCount();
    });
    
    // ===== Снять выделение =====
    $('#btn-deselect-all').on('click', function() {
        $('#left-tree .item-checkbox').prop('checked', false);
        updateSelectedCount();
    });
    
    // ===== Развернуть всё =====
    function expandAll(side) {
        var prefix = side === 'left' ? '#left-tree' : '#right-tree';
        $(prefix + ' .tree-node[data-type="org"]').each(function() {
            var $this = $(this);
            var $children = $this.children('.tree-children');
            var $icon = $this.find('.tree-toggle .glyphicon');
            
            if (!$this.data('expanded') && $children.length > 0) {
                if ($children.children().length === 0) {
                    loadNodeContent($this);
                } else {
                    $children.slideDown();
                    $icon.removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
                    $this.data('expanded', true);
                }
            }
        });
    }
    
    $('#btn-expand-left').on('click', function() { expandAll('left'); });
    $('#btn-expand-right').on('click', function() { expandAll('right'); });
    
    // ===== Свернуть всё =====
    function collapseAll(side) {
        var prefix = side === 'left' ? '#left-tree' : '#right-tree';
        $(prefix + ' .tree-children').slideUp();
        $(prefix + ' .tree-node[data-type="org"]').data('expanded', false);
        $(prefix + ' .tree-node[data-type="org"] .tree-toggle .glyphicon').removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');
    }
    
    $('#btn-collapse-left').on('click', function() { collapseAll('left'); });
    $('#btn-collapse-right').on('click', function() { collapseAll('right'); });
    
    // ===== Поиск =====
    function searchTree(side, query) {
        var prefix = side === 'left' ? '#left-tree' : '#right-tree';
        
        if (query.length < 2) {
            $(prefix + ' .tree-node').show();
            return;
        }
        
        $(prefix + ' .tree-node').each(function() {
            var text = $(this).find('.item-name').text().toLowerCase();
            if (text.indexOf(query) > -1) {
                $(this).show();
                $(this).parents('.tree-node').show();
            } else {
                $(this).hide();
            }
        });
    }
    
    $('#left-search').on('keyup', function() {
        searchTree('left', $(this).val().toLowerCase());
    });
    
    $('#right-search').on('keyup', function() {
        searchTree('right', $(this).val().toLowerCase());
    });
    
    // ===== Перемещение =====
    $('#btn-move').on('click', function() {
        var selected = $('#left-tree .item-checkbox:checked');
        if (selected.length === 0) {
            alert('<?php echo __('Не выбраны элементы для перемещения'); ?>');
            return;
        }
        
        if (!targetOrgId) {
            alert('<?php echo __('Выберите целевую организацию в правой панели'); ?>');
            return;
        }
        
        var peopleIds = [];
        var orgIds = [];
        
        selected.each(function() {
            var type = $(this).data('type');
            var id = parseInt($(this).data('id'));
            if (type === 'person') {
                peopleIds.push(id);
            } else if (type === 'org') {
                if (id === 1) {
                    alert('<?php echo __('Нельзя перемещать корневую организацию'); ?>');
                    return;
                }
                orgIds.push(id);
            }
        });
        
        var total = peopleIds.length + orgIds.length;
        if (total === 0) {
            alert('<?php echo __('Не выбраны элементы для перемещения'); ?>');
            return;
        }
        
        if (!confirm('Переместить ' + total + ' элементов в организацию "' + targetOrgName + '" (ID: ' + targetOrgId + ')?')) {
            return;
        }
        
        $.ajax({
            url: '<?php echo URL::site('mancard/move_items'); ?>',
            type: 'POST',
            data: {
                target_org_id: targetOrgId,
                move_people: peopleIds,
                move_orgs: orgIds
            },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-move').prop('disabled', true).html('<span class="glyphicon glyphicon-refresh glyphicon-spin" style="font-size: 2em;"></span><br><small><?php echo __('Выполняется...'); ?></small>');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Ошибка: ' + response.message);
                    $('#btn-move').prop('disabled', false).html('<span class="glyphicon glyphicon-arrow-right" style="font-size: 2em;"></span><br><small><?php echo __('Переместить →'); ?></small>');
                }
            },
            error: function() {
                alert('<?php echo __('Ошибка при выполнении запроса'); ?>');
                $('#btn-move').prop('disabled', false).html('<span class="glyphicon glyphicon-arrow-right" style="font-size: 2em;"></span><br><small><?php echo __('Переместить →'); ?></small>');
            }
        });
    });
    
    // ===== Обновление =====
    $('#btn-refresh').on('click', function() {
        location.reload();
    });
    
    // ===== Переключение вида =====
    $('#btn-switch-view').on('click', function() {
        window.location.href = '<?php echo URL::site('mancard/index'); ?>';
    });
    
    // ===== Инициализация =====
    // Закрываем все папки кроме корня
    $('.tree-children').hide();
    $('.tree-node[data-type="org"]').data('expanded', false);
    
    // Открываем корень в левой панели
    $('#left-tree-root .tree-children').show();
    $('#left-tree-root .tree-node').data('expanded', true);
    $('#left-tree-root .tree-toggle .glyphicon').removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
    
    // Открываем корень в правой панели
    $('#right-tree-root .tree-children').show();
    $('#right-tree-root .tree-node').data('expanded', true);
    $('#right-tree-root .tree-toggle .glyphicon').removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
    
    updateSelectedCount();
});
// ===== Инициализация Bootstrap Tooltips =====
// Инициализируем все существующие tooltips
$('[data-toggle="tooltip"]').tooltip({
    placement: 'top',
    trigger: 'hover',
    container: 'body'
});

// Для динамически добавляемых элементов - используем делегирование
$(document).on('mouseenter', '[data-toggle="tooltip"]', function() {
    var $this = $(this);
    // Если tooltip еще не инициализирован
    if (!$this.data('bs.tooltip')) {
        $this.tooltip({
            placement: 'top',
            trigger: 'hover',
            container: 'body'
        });
    }
});


</script>