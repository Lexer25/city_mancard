<?php
$isRoot = ($org['ID_ORG'] == 1);
$peopleCount = isset($org['PEOPLE_COUNT']) ? (int)$org['PEOPLE_COUNT'] : 0;
$childrenCount = isset($org['CHILDREN']) ? count($org['CHILDREN']) : 0;
$hasChildren = ($childrenCount > 0);
$hasPeople = isset($org['PEOPLE']) && !empty($org['PEOPLE']);
?>
<li class="tree-node" data-org-id="<?php echo $org['ID_ORG']; ?>" data-type="org" data-expanded="<?php echo $isRoot ? 'true' : 'false'; ?>">
    <div class="tree-item tree-item-org" data-org-id="<?php echo $org['ID_ORG']; ?>" data-org-name="<?php echo htmlspecialchars($org['NAME']); ?>" style="cursor: pointer;">
        <span class="tree-toggle">
            <span style="font-size: 18px; color: #f5c842;"><?php echo $isRoot ? '📂' : '📁'; ?></span>
        </span>
        <span class="item-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        
        <!-- Бейдж с количеством сотрудников -->
        <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7;">
            👤 <?php echo $peopleCount; ?>
        </span>
        
        <!-- Бейдж с количеством дочерних организаций -->
        <?php if ($childrenCount > 0): ?>
            <span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de;">
                📁 <?php echo $childrenCount; ?>
            </span>
        <?php endif; ?>
    </div>
    
   
    
    <!-- Дочерние организации -->
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
	 <!-- ===== КОРНЕВЫЕ ПИПЛЫ (БЕЗ ЧЕКБОКСОВ) ===== -->
    <?php if ($isRoot && $hasPeople): ?>
        <ul class="tree-children" style="display: block;">
            <?php foreach ($org['PEOPLE'] as $person): ?>
                <li class="tree-node" data-person-id="<?php echo $person['ID_PEP']; ?>" data-type="person">
                    <div class="tree-item tree-item-person" style="cursor: default;">
                        <span class="tree-toggle">
                            <span style="font-size: 16px;">👤</span>
                        </span>
                        <span class="item-name">
                            <?php echo htmlspecialchars($person['SURNAME'] . ' ' . $person['NAME'] . ' ' . $person['PATRONYMIC']); ?>
                        </span>
                        <?php if (!empty($person['POST'])): ?>
                            <span class="person-post" style="color: #999; font-size: 11px; margin-left: 5px;">
                                (<?php echo htmlspecialchars($person['POST']); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>