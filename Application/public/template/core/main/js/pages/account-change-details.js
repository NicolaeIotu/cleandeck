'use strict'

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

function resizeLiveImage(target_image) {
    target_image.setAttribute('width', target_image.naturalWidth)
    target_image.setAttribute('height', target_image.naturalHeight)
}

const picture_sub_holder = document.getElementById('picture_sub_holder')
const default_img = document.getElementById('profile_picture_img')
const preview_img = default_img.cloneNode()
const default_pictures_field = document.getElementById('pictures')
const cont_remove_picture = document.getElementById('cont-remove-picture')
const remove_picture = document.getElementById('remove-picture')
const has_pic = document.getElementById('has_pic')

const img_src_add = picture_sub_holder.getAttribute('data-img-src-add')

default_img.onload = function () {
    void resizeLiveImage(this)
}
picture_sub_holder.onclick = function () {
    const pf_clone = default_pictures_field.cloneNode()
    pf_clone.onchange = function () {
        has_pic.setAttribute('value', '1')
        this.setAttribute('form', 'main')
        document.getElementById('pictures').replaceWith(this)
        void buildPreview(this.files)
    }
    void pf_clone.click()
}
remove_picture.onclick = function () {
    void removePicture()
}


function removePicture() {
    default_img.src = img_src_add;
    document.getElementById('pictures').removeAttribute('form');
    document.getElementById('profile_picture_img').replaceWith(default_img);
    has_pic.setAttribute('value', '0');
}

// valid for this use case only!
function resizeDimensions(fromWidth, fromHeight, fitWidth = 128, fitHeight = 128) {
    if (fromWidth <= fitWidth && fromHeight <= fitHeight) {
        return {
            width: fromWidth,
            height: fromHeight
        };
    }

    const resize_factor = Math.max(fromWidth / fitWidth, fromHeight / fitHeight)

    return {
        width: Math.min(128, fromWidth / resize_factor),
        height: Math.min(128, fromHeight / resize_factor)
    };
}

function imgOnLoad() {
    const new_dims = resizeDimensions(preview_img.naturalWidth, preview_img.naturalHeight);
    preview_img.setAttribute('width', new_dims.width);
    preview_img.setAttribute('height', new_dims.height);

    document.getElementById('profile_picture_img').replaceWith(preview_img);
}

preview_img.onload = imgOnLoad

function buildPreview(files) {
    cont_remove_picture.classList.remove('d-none');

    if (files.length === 0) {
        preview_img.setAttribute("width", preview_img.naturalWidth.toString());
        preview_img.setAttribute("height", preview_img.naturalHeight.toString());
    } else {
        preview_img.src = URL.createObjectURL(files[0]);
    }
}
