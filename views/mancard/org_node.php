<?php
/**
 * Рекурсивный рендеринг узла дерева организаций
 * @var array $org - данные организации
 */
?>
<li>
    <div class="org-node" data-org-id="<?php echo $org['ID_ORG']; ?>">
        <span class="tree-toggle">
            <span class="glyphicon glyphicon-folder-close"></span>
        </span>
        <span class="org-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        <span class="badge" id="org-count-<?php echo $org['ID_ORG']; ?>">
            <?php echo isset($org['PEOPLE_COUNT']) ? $org['PEOPLE_COUNT'] : 0; ?>
        </span>
        <div class="org-actions pull-right">
            <button class="btn btn-xs btn-info btn-add-child" title="<?php echo __('Добавить подразделение'); ?>">
                <span class="glyphicon glyphicon-plus"></span>
            </button>
            <?php if ($org['ID_ORG'] != 1): ?>
                <button class="btn btn-xs btn-danger btn-delete-org" title="<?php echo __('Удалить организацию'); ?>">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php if (isset($org['CHILDREN']) && !empty($org['CHILDREN'])): ?>
        <ul class="list-unstyled org-children" data-parent="<?php echo $org['ID_ORG']; ?>">
            <?php foreach ($org['CHILDREN'] as $child): ?>
                <?php echo View::factory('mancard/tree_node_with_cards', array(
    'org' => $org,
    'level' => 0
)); ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>