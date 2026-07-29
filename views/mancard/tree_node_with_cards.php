<?php
/**
 * Рекурсивный рендеринг узла дерева с отображением карт сотрудников
 * @var array $org - данные организации
 * @var int $level - уровень вложенности
 */
$isRoot = ($org['ID_ORG'] == 1);
?>
<li class="tree-node" data-org-id="<?php echo $org['ID_ORG']; ?>" data-type="org" data-expanded="<?php echo $isRoot ? 'true' : 'false'; ?>">
    <div class="tree-item tree-item-org">
        <span class="tree-toggle">
            <span class="glyphicon <?php echo $isRoot ? 'glyphicon-folder-open' : 'glyphicon-folder-close'; ?>"></span>
        </span>
        <span class="item-name org-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        <?php 
        $people_count = 0;
        if (isset($org['PEOPLE_COUNT']) && $org['PEOPLE_COUNT'] > 0) {
            $people_count = $org['PEOPLE_COUNT'];
        }
        ?>
        <?php if ($people_count > 0): ?>
            <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7;">
                <span class="glyphicon glyphicon-user"></span> <?php echo $people_count; ?>
            </span>
        <?php endif; ?>
        <?php if (isset($org['CHILDREN']) && !empty($org['CHILDREN'])): ?>
            <span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de;">
                <span class="glyphicon glyphicon-folder-close"></span> <?php echo count($org['CHILDREN']); ?>
            </span>
        <?php endif; ?>
        <div class="org-actions pull-right">
            <button class="btn btn-xs btn-info btn-add-child" title="<?php echo __('Добавить подразделение'); ?>">
                <span class="glyphicon glyphicon-plus"></span>
            </button>
            <button class="btn btn-xs btn-warning btn-rename-org" title="<?php echo __('Переименовать'); ?>">
                <span class="glyphicon glyphicon-pencil"></span>
            </button>
            <?php if ($org['ID_ORG'] != 1): ?>
                <button class="btn btn-xs btn-danger btn-delete-org" title="<?php echo __('Удалить организацию'); ?>">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>
            <?php endif; ?>
        </div>
        <span class="id-tooltip">ID_ORG: <?php echo $org['ID_ORG']; ?></span>
    </div>
    
    <?php if (isset($org['CHILDREN']) && !empty($org['CHILDREN'])): ?>
        <ul class="tree-children" style="display: <?php echo $isRoot ? 'block' : 'none'; ?>;">
            <?php foreach ($org['CHILDREN'] as $child): ?>
                <?php echo View::factory('mancard/tree_node_with_cards', array(
                    'org' => $child,
                    'level' => $level + 1
                )); ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <ul class="tree-children" style="display: none;"></ul>
    <?php endif; ?>
</li>