<?php
/**
 * Рендеринг узла дерева для правой панели (Назначение)
 * Без чекбоксов, с возможностью выбора кликом
 * @var array $org - данные организации
 * @var int $level - уровень вложенности
 */
$isRoot = ($org['ID_ORG'] == 1);
$peopleCount = isset($org['PEOPLE_COUNT']) ? (int)$org['PEOPLE_COUNT'] : 0;
$childrenCount = isset($org['CHILDREN']) ? count($org['CHILDREN']) : 0;
$hasChildren = ($childrenCount > 0);

// Формируем текст для подсказок
$peopleTooltip = $peopleCount . ' сотрудников в организации';
$childrenTooltip = $childrenCount . ' дочерних организаций';
?>
<li class="tree-node" data-org-id="<?php echo $org['ID_ORG']; ?>" data-type="org" data-expanded="<?php echo $isRoot ? 'true' : 'false'; ?>">
    <div class="tree-item tree-item-org" data-org-id="<?php echo $org['ID_ORG']; ?>" data-org-name="<?php echo htmlspecialchars($org['NAME']); ?>" style="cursor: pointer;">
        <span class="tree-toggle">
            <span class="glyphicon <?php echo $isRoot ? 'glyphicon-folder-open' : 'glyphicon-folder-close'; ?>"></span>
        </span>
        <span class="item-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        
        <!-- Бейдж с количеством сотрудников -->
        <?php if ($peopleCount > 0): ?>
            <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7; cursor: help;" 
                  title="<?php echo $peopleTooltip; ?>" 
                  data-toggle="tooltip" data-placement="top">
                <span class="glyphicon glyphicon-user"></span> <?php echo $peopleCount; ?>
            </span>
        <?php else: ?>
            <span class="badge badge-empty" style="margin-left: 5px; font-size: 10px; background-color: #f5f5f5; color: #ccc; cursor: help;" 
                  title="Нет сотрудников в организации" 
                  data-toggle="tooltip" data-placement="top">
                <span class="glyphicon glyphicon-user"></span> 0
            </span>
        <?php endif; ?>
        
        <!-- Бейдж с количеством дочерних организаций -->
        <?php if ($childrenCount > 0): ?>
            <span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de; cursor: help;" 
                  title="<?php echo $childrenTooltip; ?>" 
                  data-toggle="tooltip" data-placement="top">
                <span class="glyphicon glyphicon-folder-close"></span> <?php echo $childrenCount; ?>
            </span>
        <?php else: ?>
            <span class="badge badge-empty" style="margin-left: 3px; font-size: 10px; background-color: #f5f5f5; color: #ccc; cursor: help;" 
                  title="Нет дочерних организаций" 
                  data-toggle="tooltip" data-placement="top">
                <span class="glyphicon glyphicon-folder-close"></span> 0
            </span>
        <?php endif; ?>
    </div>
    
    <?php if ($hasChildren): ?>
        <ul class="tree-children" style="display: <?php echo $isRoot ? 'block' : 'none'; ?>;">
            <?php foreach ($org['CHILDREN'] as $child): ?>
                <?php echo View::factory('mancard/move_to_tree_node_with_cards', array(
                    'org' => $child,
                    'level' => $level + 1
                )); ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <ul class="tree-children" style="display: none;"></ul>
    <?php endif; ?>
</li>