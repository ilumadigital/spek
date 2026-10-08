(function ($) {
    'use strict';

    $(function () {
        const editor = $('#spek_product_gallery_editor');
        if (!editor.length) {
            return;
        }

        const input = $('#spek_product_gallery_ids');
        const preview = $('#spek_product_gallery_preview');
        const choose = $('#spek_product_gallery_choose');
        const clear = $('#spek_product_gallery_clear');
        const labels = window.spekProductGalleryI18n || {};
        let galleryFrame;
        let replaceFrame;
        let replaceTarget = null;

        function currentIds() {
            return (input.val() || '')
                .split(',')
                .map(function (id) { return parseInt(id, 10); })
                .filter(function (id, index, ids) {
                    return Number.isInteger(id) && id > 0 && ids.indexOf(id) === index;
                });
        }

        function syncInput() {
            const ids = preview.children('[data-id]').map(function () {
                return parseInt($(this).attr('data-id'), 10);
            }).get().filter(function (id) { return Number.isInteger(id) && id > 0; });
            input.val(ids.join(',')).trigger('change');
            clear.toggle(ids.length > 0);
            preview.toggleClass('is-empty', !ids.length);
        }

        function makeItem(attachment) {
            const data = attachment && typeof attachment.toJSON === 'function'
                ? attachment.toJSON()
                : attachment;
            if (!data || !data.id) {
                return null;
            }

            const thumbnail = (data.sizes && (data.sizes.thumbnail || data.sizes.medium)) || {};
            const src = thumbnail.url || data.icon || data.url || '';
            const item = $('<li/>', {
                class: 'spek-product-gallery-item',
                'data-id': data.id
            });
            const image = $('<img/>', {
                src: src,
                alt: data.alt || data.title || '',
                width: 96,
                height: 96
            });
            const controls = $('<div/>', { class: 'spek-product-gallery-item__controls' });

            [
                ['replace', labels.replace || 'Αλλαγή'],
                ['remove', labels.remove || 'Αφαίρεση'],
                ['up', labels.up || 'Πριν'],
                ['down', labels.down || 'Μετά']
            ].forEach(function (control) {
                $('<button/>', {
                    type: 'button',
                    class: 'button button-small spek-product-gallery-' + control[0],
                    text: control[1],
                    'aria-label': control[1] + ' – ' + (data.title || data.filename || data.id)
                }).appendTo(controls);
            });

            item.append(image, controls);
            return item;
        }

        function selectExisting(frame) {
            const selection = frame.state().get('selection');
            selection.reset();
            currentIds().forEach(function (id) {
                const attachment = wp.media.attachment(id);
                attachment.fetch();
                selection.add(attachment);
            });
        }

        function openGallery() {
            if (!galleryFrame) {
                galleryFrame = wp.media({
                    title: labels.galleryTitle || 'Επιλογή εικόνων gallery',
                    button: { text: labels.useSelected || 'Χρήση επιλεγμένων εικόνων' },
                    library: { type: 'image' },
                    multiple: true
                });

                galleryFrame.on('open', function () {
                    selectExisting(galleryFrame);
                });

                galleryFrame.on('select', function () {
                    const selection = galleryFrame.state().get('selection');
                    const used = new Set();
                    preview.empty();
                    selection.each(function (attachment) {
                        const id = parseInt(attachment.id, 10);
                        if (!id || used.has(id)) {
                            return;
                        }
                        used.add(id);
                        const item = makeItem(attachment);
                        if (item) {
                            preview.append(item);
                        }
                    });
                    syncInput();
                });
            }
            galleryFrame.open();
        }

        function openReplacement(id) {
            replaceTarget = id;
            if (!replaceFrame) {
                replaceFrame = wp.media({
                    title: labels.replaceTitle || 'Αλλαγή εικόνας gallery',
                    button: { text: labels.useImage || 'Χρήση εικόνας' },
                    library: { type: 'image' },
                    multiple: false
                });
                replaceFrame.on('open', function () {
                    replaceFrame.state().get('selection').reset();
                });
                replaceFrame.on('select', function () {
                    const attachment = replaceFrame.state().get('selection').first();
                    if (!attachment || replaceTarget === null) {
                        return;
                    }
                    const newId = parseInt(attachment.id, 10);
                    if (!newId) {
                        return;
                    }
                    if (newId !== replaceTarget && currentIds().indexOf(newId) !== -1) {
                        window.alert(labels.alreadySelected || 'Η εικόνα υπάρχει ήδη στο gallery.');
                        return;
                    }
                    const oldItem = preview.children('[data-id="' + replaceTarget + '"]');
                    const newItem = makeItem(attachment);
                    if (oldItem.length && newItem) {
                        oldItem.replaceWith(newItem);
                        syncInput();
                    }
                    replaceTarget = null;
                });
            }
            replaceFrame.open();
        }

        // WordPress Media Library loads through admin_enqueue_scripts; never bind
        // these handlers from inline metabox markup before wp.media is available.
        if (!window.wp || !wp.media) {
            choose.prop('disabled', true);
            preview.find('.spek-product-gallery-replace').prop('disabled', true);
        } else {
            choose.on('click', function (event) {
                event.preventDefault();
                openGallery();
            });
        }

        clear.on('click', function (event) {
            event.preventDefault();
            preview.empty();
            syncInput();
        });

        preview.on('click', '.spek-product-gallery-remove', function (event) {
            event.preventDefault();
            $(this).closest('[data-id]').remove();
            syncInput();
        });

        preview.on('click', '.spek-product-gallery-replace', function (event) {
            event.preventDefault();
            if (!window.wp || !wp.media) {
                return;
            }
            openReplacement(parseInt($(this).closest('[data-id]').attr('data-id'), 10));
        });

        preview.on('click', '.spek-product-gallery-up, .spek-product-gallery-down', function (event) {
            event.preventDefault();
            const item = $(this).closest('[data-id]');
            if ($(this).hasClass('spek-product-gallery-up')) {
                const previous = item.prev('[data-id]');
                if (previous.length) {
                    item.insertBefore(previous);
                }
            } else {
                const next = item.next('[data-id]');
                if (next.length) {
                    item.insertAfter(next);
                }
            }
            syncInput();
        });

        if ($.fn.sortable) {
            preview.sortable({
                items: '> .spek-product-gallery-item',
                distance: 5,
                placeholder: 'spek-product-gallery-placeholder',
                stop: syncInput
            });
        }

        syncInput();
    });
})(jQuery);
