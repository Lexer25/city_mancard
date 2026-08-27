<?php
$isRoot = ($org['ID_ORG'] == 1);
$peopleCount = isset($org['PEOPLE_COUNT']) ? (int)$org['PEOPLE_COUNT'] : 0;
// ===== ИСПРАВЛЕНО: используем CHILDREN_COUNT, если есть =====
$childrenCount = isset($org['CHILDREN_COUNT']) ? (int)$org['CHILDREN_COUNT'] : count($org['CHILDREN']);
$hasChildren = ($childrenCount > 0);
$hasPeople = isset($org['PEOPLE']) && !empty($org['PEOPLE']);

// Пропускаем организацию с ID_ORG = 0 (системная)
if ($org['ID_ORG'] == 0) {
    return;
}
?>
<li class="tree-node" data-org-id="<?php echo $org['ID_ORG']; ?>" data-type="org" data-expanded="<?php echo $isRoot ? 'true' : 'false'; ?>">
    <div class="tree-item tree-item-org">
        <?php if (!$isRoot): ?>
            <input type="checkbox" class="item-checkbox" id="left_org_<?php echo $org['ID_ORG']; ?>" 
                   value="org_<?php echo $org['ID_ORG']; ?>" 
                   data-type="org" 
                   data-id="<?php echo $org['ID_ORG']; ?>" 
                   data-name="<?php echo htmlspecialchars($org['NAME']); ?>">
        <?php else: ?>
            <span style="width: 20px; flex-shrink: 0;"></span>
        <?php endif; ?>
        
        <span class="tree-toggle">
            <span style="font-size: 16px;"><?php echo $isRoot ? '📂' : '📁'; ?></span>
        </span>
        
        <!-- ID_ORG перед названием -->
        <?php if ($org['ID_ORG'] != 0): ?>
            <span class="org-id" style="color: #999; font-size: 11px; font-family: monospace; margin-left: 3px;">
                [<?php echo $org['ID_ORG']; ?>]
            </span>
        <?php endif; ?>
        
        <span class="item-name"><?php echo htmlspecialchars($org['NAME']); ?></span>
        
        <!-- Бейдж с количеством сотрудников -->
        <span class="badge" style="margin-left: 5px; font-size: 10px; background-color: #337ab7;">
            👤 <?php echo $peopleCount; ?>
        </span>
        
        <!-- ===== БЕЙДЖ С КОЛИЧЕСТВОМ ДОЧЕРНИХ ОРГАНИЗАЦИЙ ===== -->
        <?php if ($childrenCount > 0): ?>
            <span class="badge" style="margin-left: 3px; font-size: 10px; background-color: #5bc0de;">
                📁 <?php echo $childrenCount; ?>
            </span>
        <?php else: ?>
            <span class="badge badge-empty" style="margin-left: 3px; font-size: 10px; background-color: #f5f5f5; color: #ccc;">
                📁 0
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Дочерние организации -->
    <?php if ($hasChildren): ?>
        <ul class="tree-children" style="display: <?php echo $isRoot ? 'block' : 'none'; ?>;">
            <?php foreach ($org['CHILDREN'] as $child): ?>
                <?php if ($child['ID_ORG'] != 0): ?>
                    <?php echo View::factory('mancard/move_from_tree_node_with_cards', array(
                        'org' => $child,
                        'level' => $level + 1
                    )); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <ul class="tree-children" style="display: none;"></ul>
    <?php endif; ?>
    
    <!-- ===== КОРНЕВЫЕ ПИПЛЫ ===== -->
    <?php if ($isRoot && $hasPeople): ?>
        <ul class="tree-children" style="display: block;">
            <?php foreach ($org['PEOPLE'] as $person): ?>
                <li class="tree-node" data-person-id="<?php echo $person['ID_PEP']; ?>" data-type="person">
                    <div class="tree-item tree-item-person">
                        <input type="checkbox" class="item-checkbox" 
                               value="person_<?php echo $person['ID_PEP']; ?>" 
                               data-type="person" 
                               data-id="<?php echo $person['ID_PEP']; ?>" 
                               data-name="<?php echo htmlspecialchars($person['SURNAME'] . ' ' . $person['NAME']); ?>">
                        <span class="tree-toggle">
                            <span style="font-size: 16px;">👤</span>
                        </span>
                        
                        <span class="person-id" style="color: #999; font-size: 11px; font-family: monospace; margin-left: 3px;">
                            [<?php echo $person['ID_PEP']; ?>]
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