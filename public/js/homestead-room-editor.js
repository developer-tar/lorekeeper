(function($) {
    'use strict';

    function HomesteadRoomEditor(options) {
        this.canvasWidth = options.canvasWidth;
        this.canvasHeight = options.canvasHeight;
        this.catalog = options.catalog || {};
        this.placements = [];
        this.placedCounts = {};
        this.selectedId = null;
        this.nextId = 1;
        this.scale = 1;
        this.dragState = null;

        this.$wrap = $(options.canvasWrapSelector);
        this.$container = $(options.canvasContainerSelector);
        this.$stage = $(options.canvasStageSelector);
        this.$items = $(options.canvasItemsSelector);
        this.$controls = $(options.controlsSelector);
        this.$inventoryRoot = $(options.inventorySelector);

        this.bindEvents();
        this.updateScale();
        if (options.initialPlacements && options.initialPlacements.length) {
            this.loadPlacements(options.initialPlacements);
        } else {
            this.render();
            this.updateInventoryCounts();
        }
    }

    HomesteadRoomEditor.prototype.getPlacements = function() {
        return this.placements.slice();
    };

    HomesteadRoomEditor.prototype.getPlacementsPayload = function() {
        return this.placements.map(function(placement) {
            return {
                item_id: placement.itemId,
                x: placement.x,
                y: placement.y,
                width: placement.width,
                height: placement.height,
                z_index: placement.zIndex,
            };
        });
    };

    HomesteadRoomEditor.prototype.loadPlacements = function(initialPlacements) {
        var self = this;
        this.placements = [];
        this.placedCounts = {};
        this.selectedId = null;
        this.nextId = 1;

        initialPlacements.forEach(function(placement) {
            var itemId = parseInt(placement.item_id, 10);
            var item = self.catalog[itemId];
            if (!item) return;

            var width = parseFloat(placement.width) || item.width;
            var height = parseFloat(placement.height) || item.height;
            var pos = self.clampPosition(
                parseFloat(placement.x),
                parseFloat(placement.y),
                width,
                height
            );

            self.placements.push({
                id: self.nextId++,
                itemId: itemId,
                x: pos.x,
                y: pos.y,
                width: width,
                height: height,
                zIndex: parseInt(placement.z_index, 10) || 10,
            });

            self.placedCounts[itemId] = (self.placedCounts[itemId] || 0) + 1;
        });

        this.render();
        this.updateInventoryCounts();
    };

    HomesteadRoomEditor.prototype.getAvailable = function(itemId) {
        var item = this.catalog[itemId];
        if (!item) return 0;
        return item.quantity - (this.placedCounts[itemId] || 0);
    };

    HomesteadRoomEditor.prototype.clampPosition = function(x, y, width, height) {
        return {
            x: Math.max(0, Math.min(x, this.canvasWidth - width)),
            y: Math.max(0, Math.min(y, this.canvasHeight - height)),
        };
    };

    HomesteadRoomEditor.prototype.updateScale = function() {
        if (!this.$container.length) return;
        var width = this.$container.innerWidth();
        var height = this.$container.innerHeight();
        if (!width || !height) return;
        this.scale = Math.min(width / this.canvasWidth, height / this.canvasHeight);
        this.$stage.css({
            width: this.canvasWidth + 'px',
            height: this.canvasHeight + 'px',
            transform: 'scale(' + this.scale + ')',
        });
    };

    HomesteadRoomEditor.prototype.toCanvasCoords = function(clientX, clientY) {
        var rect = this.$stage[0].getBoundingClientRect();
        return {
            x: (clientX - rect.left) / this.scale,
            y: (clientY - rect.top) / this.scale,
        };
    };

    HomesteadRoomEditor.prototype.placeItem = function(itemId, x, y) {
        if (this.getAvailable(itemId) <= 0) return;

        var item = this.catalog[itemId];
        var pos = this.clampPosition(
            typeof x === 'number' ? x : (this.canvasWidth / 2) - (item.width / 2),
            typeof y === 'number' ? y : (this.canvasHeight / 2) - (item.height / 2),
            item.width,
            item.height
        );

        var maxZ = 10;
        this.placements.forEach(function(placement) {
            if (placement.zIndex > maxZ) maxZ = placement.zIndex;
        });

        this.placements.push({
            id: this.nextId++,
            itemId: itemId,
            x: pos.x,
            y: pos.y,
            width: item.width,
            height: item.height,
            zIndex: maxZ + 1,
        });

        this.placedCounts[itemId] = (this.placedCounts[itemId] || 0) + 1;
        this.render();
        this.updateInventoryCounts();
    };

    HomesteadRoomEditor.prototype.removePlacement = function(placementId) {
        var placement = null;
        this.placements.forEach(function(entry) {
            if (entry.id === placementId) placement = entry;
        });
        if (!placement) return;

        this.placements = this.placements.filter(function(entry) {
            return entry.id !== placementId;
        });
        this.placedCounts[placement.itemId] = Math.max(0, (this.placedCounts[placement.itemId] || 1) - 1);

        if (this.selectedId === placementId) {
            this.selectedId = null;
        }

        this.render();
        this.updateInventoryCounts();
    };

    HomesteadRoomEditor.prototype.selectPlacement = function(placementId) {
        this.selectedId = placementId;
        this.render();
    };

    HomesteadRoomEditor.prototype.clearSelection = function() {
        this.selectedId = null;
        this.render();
    };

    HomesteadRoomEditor.prototype.adjustZIndex = function(placementId, delta) {
        var placement = null;
        this.placements.forEach(function(entry) {
            if (entry.id === placementId) placement = entry;
        });
        if (!placement) return;

        placement.zIndex = Math.max(1, placement.zIndex + delta);
        this.render();
    };

    HomesteadRoomEditor.prototype.render = function() {
        var self = this;
        this.$items.empty();

        this.placements.slice().sort(function(a, b) {
            return a.zIndex - b.zIndex;
        }).forEach(function(placement) {
            var item = self.catalog[placement.itemId];
            if (!item) return;

            var $node = $('<div class="homestead-editor-placed-item"></div>');
            $node.attr({
                'data-placement-id': placement.id,
                'data-item-id': placement.itemId,
            });
            $node.css({
                left: placement.x + 'px',
                top: placement.y + 'px',
                width: placement.width + 'px',
                height: placement.height + 'px',
                zIndex: placement.zIndex,
            });

            if (self.selectedId === placement.id) {
                $node.addClass('is-selected');
            }

            if (item.hasImage && item.imageUrl) {
                $('<img>', {
                    src: item.imageUrl,
                    alt: item.name,
                    draggable: false,
                }).appendTo($node);
            } else {
                $('<div class="homestead-editor-placed-item-fallback"><i class="fas fa-cube"></i></div>').appendTo($node);
            }

            self.$items.append($node);
        });

        if (this.selectedId) {
            this.$controls.removeClass('d-none');
        } else {
            this.$controls.addClass('d-none');
        }
    };

    HomesteadRoomEditor.prototype.updateInventoryCounts = function() {
        var self = this;
        this.$inventoryRoot.find('.homestead-editor-inventory-item.is-placeable').each(function() {
            var $item = $(this);
            var itemId = parseInt($item.data('item-id'), 10);
            var available = self.getAvailable(itemId);
            var total = self.catalog[itemId] ? self.catalog[itemId].quantity : 0;
            var placed = self.placedCounts[itemId] || 0;

            $item.toggleClass('is-unavailable', available <= 0);

            if (placed > 0) {
                $item.find('.homestead-editor-inventory-item-qty').text(available + ' / ' + total);
            } else {
                $item.find('.homestead-editor-inventory-item-qty').text('x' + total);
            }
        });
    };

    HomesteadRoomEditor.prototype.bindEvents = function() {
        var self = this;

        $(window).on('resize.homesteadEditor', function() {
            self.updateScale();
        });

        this.$inventoryRoot.on('click', '.homestead-editor-inventory-item.is-placeable', function(e) {
            e.preventDefault();
            var $item = $(this);
            if ($item.hasClass('is-unavailable')) return;
            self.placeItem(parseInt($item.data('item-id'), 10));
        });

        this.$items.on('mousedown touchstart', '.homestead-editor-placed-item', function(e) {
            if (e.type === 'mousedown' && e.which !== 1) return;
            e.preventDefault();
            e.stopPropagation();

            var placementId = parseInt($(this).data('placement-id'), 10);
            var placement = null;
            self.placements.forEach(function(entry) {
                if (entry.id === placementId) placement = entry;
            });
            if (!placement) return;

            var point = self.getEventPoint(e);
            var coords = self.toCanvasCoords(point.x, point.y);

            self.selectPlacement(placementId);
            self.dragState = {
                placementId: placementId,
                offsetX: coords.x - placement.x,
                offsetY: coords.y - placement.y,
            };
        });

        $(document).on('mousemove.homesteadEditor touchmove.homesteadEditor', function(e) {
            if (!self.dragState) return;
            e.preventDefault();

            var placement = null;
            self.placements.forEach(function(entry) {
                if (entry.id === self.dragState.placementId) placement = entry;
            });
            if (!placement) return;

            var point = self.getEventPoint(e);
            var coords = self.toCanvasCoords(point.x, point.y);
            var pos = self.clampPosition(
                coords.x - self.dragState.offsetX,
                coords.y - self.dragState.offsetY,
                placement.width,
                placement.height
            );

            placement.x = pos.x;
            placement.y = pos.y;
            self.render();
        });

        $(document).on('mouseup.homesteadEditor touchend.homesteadEditor touchcancel.homesteadEditor', function() {
            self.dragState = null;
        });

        this.$stage.on('mousedown touchstart', function(e) {
            if ($(e.target).closest('.homestead-editor-placed-item').length) return;
            self.clearSelection();
        });

        this.$controls.on('click', '[data-editor-action]', function(e) {
            e.preventDefault();
            if (!self.selectedId) return;

            var action = $(this).data('editor-action');
            if (action === 'remove') {
                self.removePlacement(self.selectedId);
            } else if (action === 'forward') {
                self.adjustZIndex(self.selectedId, 1);
            } else if (action === 'backward') {
                self.adjustZIndex(self.selectedId, -1);
            }
        });

        $(document).on('keydown.homesteadEditor', function(e) {
            if (!self.selectedId) return;
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if ($(e.target).is('input, textarea')) return;
                e.preventDefault();
                self.removePlacement(self.selectedId);
            }
        });
    };

    HomesteadRoomEditor.prototype.getEventPoint = function(e) {
        if (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches.length) {
            return {
                x: e.originalEvent.touches[0].clientX,
                y: e.originalEvent.touches[0].clientY,
            };
        }
        if (e.originalEvent && e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
            return {
                x: e.originalEvent.changedTouches[0].clientX,
                y: e.originalEvent.changedTouches[0].clientY,
            };
        }
        return {
            x: e.clientX,
            y: e.clientY,
        };
    };

    window.HomesteadRoomEditor = HomesteadRoomEditor;
})(jQuery);
