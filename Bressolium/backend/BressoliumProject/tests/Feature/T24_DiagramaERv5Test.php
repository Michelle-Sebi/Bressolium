<?php

// ==========================================
// TEST FOR: TASK 24 (Raw_Tareas)
// Title: Update ER Diagram to V5
// Area: DOCUMENTACIÓN
// ==========================================
// Verifica que el diagrama ER ha sido actualizado con todas las nuevas
// tablas y columnas introducidas en las Tareas 21 y 22.

$diagramPath = base_path('../../Documentacion/diagramas/ER_v4.html');

test('el diagrama ER existe en la ruta de documentación', function () use ($diagramPath) {
    expect(file_exists($diagramPath))->toBeTrue(
        "No se encontró el diagrama ER en: $diagramPath"
    );
});

test('el diagrama ER refleja la nueva columna base_type de tile_types (T21)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('base_type');
});

test('el diagrama ER refleja las columnas de exploración de tiles (T21)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('explored_by_player_id')
        ->and($content)->toContain('explored_at');
});

test('el diagrama ER refleja las columnas tier y group de materials (T21)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('tier')
        ->and($content)->toContain('group');
});

test('el diagrama ER refleja las nuevas tablas de prerequisitos (T22)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('invention_prerequisites')
        ->and($content)->toContain('technology_prerequisites');
});

test('el diagrama ER refleja la tabla invention_costs separada de recipes (T22)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('invention_costs');
});

test('el diagrama ER refleja las tablas de bonificadores (T22)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('technology_bonuses')
        ->and($content)->toContain('invention_bonuses');
});

test('el diagrama ER refleja las tablas de desbloqueos (T22)', function () use ($diagramPath) {
    $content = file_get_contents($diagramPath);
    expect($content)->toContain('technology_unlocks')
        ->and($content)->toContain('invention_unlocks');
});
