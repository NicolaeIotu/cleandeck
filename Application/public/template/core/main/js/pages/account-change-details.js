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

function resizeLiveImage (targetImage) {
  targetImage.setAttribute('width', targetImage.naturalWidth)
  targetImage.setAttribute('height', targetImage.naturalHeight)
}

const pictureSubHolder = document.getElementById('picture_sub_holder')
const defaultImg = document.getElementById('profile_picture_img')
const previewImg = defaultImg.cloneNode()
const defaultPicturesField = document.getElementById('pictures')
const contRemovePicture = document.getElementById('cont-remove-picture')
const removePictureElement = document.getElementById('remove-picture')
const hasPic = document.getElementById('has_pic')

const imgSrcAdd = pictureSubHolder.getAttribute('data-img-src-add')

defaultImg.onload = function () {
  resizeLiveImage(this)
}
pictureSubHolder.onclick = function () {
  const pfClone = defaultPicturesField.cloneNode()
  pfClone.onchange = function () {
    hasPic.setAttribute('value', '1')
    this.setAttribute('form', 'main')
    document.getElementById('pictures').replaceWith(this)
    buildPreview(this.files)
  }
  pfClone.click()
}
removePictureElement.onclick = function () {
  removePicture()
}

function removePicture () {
  defaultImg.src = imgSrcAdd
  document.getElementById('pictures').removeAttribute('form')
  document.getElementById('profile_picture_img').replaceWith(defaultImg)
  hasPic.setAttribute('value', '0')
}

// valid for this use case only!
function resizeDimensions (fromWidth, fromHeight, fitWidth = 128, fitHeight = 128) {
  if (fromWidth <= fitWidth && fromHeight <= fitHeight) {
    return {
      width: fromWidth,
      height: fromHeight
    }
  }

  const resizeFactor = Math.max(fromWidth / fitWidth, fromHeight / fitHeight)

  return {
    width: Math.min(128, fromWidth / resizeFactor),
    height: Math.min(128, fromHeight / resizeFactor)
  }
}

function imgOnLoad () {
  const newDims = resizeDimensions(previewImg.naturalWidth, previewImg.naturalHeight)
  previewImg.setAttribute('width', newDims.width)
  previewImg.setAttribute('height', newDims.height)

  document.getElementById('profile_picture_img').replaceWith(previewImg)
}

previewImg.onload = imgOnLoad

function buildPreview (files) {
  contRemovePicture.classList.remove('d-none')

  if (files.length === 0) {
    previewImg.setAttribute('width', previewImg.naturalWidth.toString())
    previewImg.setAttribute('height', previewImg.naturalHeight.toString())
  } else {
    previewImg.src = URL.createObjectURL(files[0])
  }
}
