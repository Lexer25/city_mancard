<?php
/**
 * Рендеринг узла дерева для левой панели (Источник)
 * С чекбоксами для выбора элементов
 * @var array $org - данные организации
 * @var int $level - уровень вложенности
 */
$isRoot = ($org['ID_ORG'] == 1);
$peopleCount = isset($org['PEOPLE_COUNT']) ? (int)$org['PEOPLE_COUNT'] : 0;
$childrenCount = isset($org['CHILDREN']) ? count($org['CHILDREN']) : 0;
$hasChildren = ($childrenCount > 0);
$checkboxId = 'left_org_' . $org['ID_ORG'];

$peopleTooltip = $peopleCount . ' сотрудников в организации';
$childrenTooltip = $childrenCount . ' дочерних организаций';
?>
<li class="tree-node" data-org-id="<?php echo $org['ID_ORG']; ?>" data-type="org" data-expanded="<?php echo $isRoot ? 'true' : 'false'; ?>">
    <div class="tree-item tree-item-org">
        <?php if (!$isRoot): ?>
            <input type="checkbox" class="item-checkbox" id="<?php echo $checkboxId; ?>" 
                   value="org_<?php echo $org['ID_ORG']; ?>" 
                   data-type="org" 
                   data-id="<?php echo $org['ID_ORG']; ?>" 
                   data-name="<?php echo htmlspecialchars($org['NAME']); ?>">
        <?php else: ?>
            <span style="width: 20px; flex-shrink: 0;"></span>
        <?php endif; ?>
        
        <span class="tree-toggle">
            <span class="glyphicon <?php echo $isRoot ? 'glyphicon-folder-open' : 'glyphicon-folder-close'; ?>"></span>
        </span>
        <span class="item-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        
        <!-- Бейдж с количеством сотрудников -->
        <?php if ($peopleCount > 0): ?>
            <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7;" 
                  data-toggle="tooltip" data-placement="top" title="<?php echo $peopleTooltip; ?>">
                <span class="glyphicon glyphicon-user"></span> <?php echo $peopleCount; ?>
            </span>
        <?php else: ?>
            <span class="badge badge-empty" style="margin-left: 5px; font-size: 10px; background-color: #f5f5f5; color: #ccc;" 
                  data-toggle="tooltip" data-placement="top" title="Нет сотрудников в организации">
                <span class="glyphicon glyphicon-user"></span> 0
            </span>
        <?php endif; ?>
        
        <!-- Бейдж с количеством дочерних организаций -->
        <?php if ($childrenCount > 0): ?>
            <span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de;" 
                  data-toggle="tooltip" data-placement="top" title="<?php echo $childrenTooltip; ?>">
                <span class="glyphicon glyphicon-folder-close"></span> <?php echo $childrenCount; ?>
            </span>
        <?php else: ?>
            <span class="badge badge-empty" style="margin-left: 3px; font-size: 10px; background-color: #f5f5f5; color: #ccc;" 
                  data-toggle="tooltip" data-placement="top" title="Нет дочерних организаций">
                <span class="glyphicon glyphicon-folder-close"></span> 0
            </span>
        <?php endif; ?>
    </div>
    
    <?php if ($hasChildren): ?>
        <ul class="tree-children" style="display: <?php echo $isRoot ? 'block' : 'none'; ?>;">
            <?php foreach ($org['CHILDREN'] as $child): ?>
                <?php echo View::factory('mancard/move_from_tree_node_with_cards', array(
                    'org' => $child,
                    'level' => $level + 1
                )); ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <ul class="tree-children" style="display: none;"></ul>
    <?php endif; ?>
</li>