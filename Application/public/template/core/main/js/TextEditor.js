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

/* global DocumentFragment */
/* global HTMLDivElement */

export default class TextEditor {
  /**
   * @member {string}
   */
  #text

  /**
   * @member {HTMLDivElement}
   */
  #parentDiv

  /**
   * @member {Object}
   */
  #htmlElements = {}

  /**
   * @member {boolean}
   */
  #visualEditorActive = true

  /**
   * @member {string}
   */
  #activeVerticalSize = 'medium'

  #verticalSizes = new Map([
    ['small', 20],
    ['medium', 40],
    ['large', 60],
    ['x-large', 80]
  ])

  /**
   * @member {boolean}
   */
  #enableEditorContours = false

  /**
   * @member {Range}
   */
  #lastRange0

  /**
   * @member {File[]|FileList}
   */
  #attachments

  static #htmlTemplateVisualMenu = '<ul class="nav nav-pills m-0 my-2">\n' +
    '<li class="nav-item"><button type="button" class="nav-link active" title="Visual Editor">Visual Editor</button></li>\n' +
    '<li class="nav-item"><button type="button" class="nav-link" title="Fine Tune HTML Code">Code Editor</button></li>\n' +
    '</ul>\n' +
    '<ul class="nav nav-pills m-0">\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0 fw-bold"><button type="button" class="nav-link text-bg-light border fs-5" title="Bold">B</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fst-italic fs-5" title="Italic">i</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0 underline"><button type="button" class="nav-link text-bg-light border fs-5" title="Underline">U</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Unordered List">ul</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Ordered List">ol</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0 underline"><button type="button" class="nav-link text-bg-light border fs-5" title="link">link</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Paragraph">&para;</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Image">img</button></li>\n' +
    '<li class="nav-item dropdown m-0 mb-2 me-2 p-0">\n' +
    '<a class="nav-link border fs-5 text-bg-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Headings">H</a>\n' +
    '<ul class="dropdown-menu dropdown-menu-cleandeck-narrow text-center text-bg-light">\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="Heading 1">h1</span></li>\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="Heading 2">h2</span></li>\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="Heading 3">h3</span></li>\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="Heading 4">h4</span></li>\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="Heading 5">h5</span></li>\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="Heading 6">h6</span></li>\n' +
    '</ul>\n' +
    '</li>\n' +
    '<li class="nav-item dropdown m-0 mb-2 me-2 p-0">\n' +
    '<a class="nav-link border fs-5 text-bg-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="HTML Tags"><></a>\n' +
    '<ul class="dropdown-menu dropdown-menu-cleandeck-medium text-center text-bg-light">\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="HTML Div">div</span></li>\n' +
    '<li class="m-0 p-0 cursor-pointer"><span class="dropdown-item fs-5" title="HTML Span">span</span></li>\n' +
    '</ul>\n' +
    '</li>\n' +
    '</ul>\n' +
    '<article contenteditable="true" class="w-100 m-0 p-2 overflow-y-scroll border border-primary border-2 rounded"></article>\n' +
    '<textarea class="d-none w-100 m-0 p-2 border border-primary border-2 rounded"></textarea>\n' +
    '<dialog class="border-1 border-secondary p-0 w-100 w-md-75 w-lg-50 w-xl-25">\n' +
    '<div class="container-fluid m-0 p-0">\n' +
    '<div class="row m-0 py-2 px-4 border-bottom">\n' +
    '<p class="fs-5 m-0 p-0 text-primary">New URL</p>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2">\n' +
    '<label class="float-start">\n' +
    'URL \n' +
    '<input type="text" class="form-input float-end w-75" autocomplete="on"/>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2">\n' +
    '<label class="float-start">\n' +
    'Content \n' +
    '<input type="text" class="form-input float-end w-75" autocomplete="on"/>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2">\n' +
    '<label class="float-start">\n' +
    'Target \n' +
    '<select class="form-select float-end w-75 px-2 py-0">\n' +
    '<option selected value="_blank">_blank</option>\n' +
    '<option value="_self">_self</option>\n' +
    '<option value="_parent">_parent</option>\n' +
    '<option value="_top">_top</option>\n' +
    '</select>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2 border border-top">\n' +
    '<button type="button" class="col btn btn-light border mx-1">Cancel</button>\n' +
    '<button type="button" class="col btn btn-primary mx-1">Add URL</button>\n' +
    '</div>\n' +
    '</div>\n' +
    '</div>\n' +
    '</dialog>\n' +
    '<dialog class="border-1 border-secondary p-0 w-100 w-md-75 w-lg-50 w-xl-25">\n' +
    '<div class="container-fluid m-0 p-0">\n' +
    '<div class="row m-0 py-2 px-4 border-bottom">\n' +
    '<p class="fs-5 m-0 p-0 text-primary">New Image</p>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2">\n' +
    '<label class="float-start">\n' +
    'Attachments\n' +
    '<select class="form-select float-end w-75 px-2 py-0">\n' +
    '<option></option>\n' +
    '</select>\n' +
    '</label>\n' +
    '<span class="small clearfix">(Uses files added using section Attachments below)</span>\n' +
    '</div>\n' +
    '<div class="row m-0 px-3">\n' +
    '<p class="m-0 p-0 fw-bold">, or</p>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2 border-bottom">\n' +
    '<label class="float-start">\n' +
    'Source\n' +
    '<input type="text" class="form-input float-end w-75" autocomplete="on"/>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2 border-bottom">\n' +
    '<label class="float-start">\n' +
    'Alt\n' +
    '<input type="text" class="form-input float-end w-75" autocomplete="on"/>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2">\n' +
    '<label class="float-start">\n' +
    'Width \n' +
    '<input type="number" min="2" step="1" class="form-input w-50 float-end text-end"/>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2">\n' +
    '<label class="float-start">\n' +
    'Height \n' +
    '<input type="number" min="2" step="1" class="form-input w-50 float-end text-end"/>\n' +
    '</label>\n' +
    '</div>\n' +
    '<div class="row m-0 p-2 border border-top">\n' +
    '<button type="button" class="col btn btn-light border mx-1">Cancel</button>\n' +
    '<button type="button" class="col btn btn-primary mx-1">Add</button>\n' +
    '</div>\n' +
    '</div>\n' +
    '</div>\n' +
    '</dialog>\n' +
    '<div class="card w-100 m-0 mt-1 p-0 small">\n' +
    '<div class="card-header m-0 my-1 py-0 px-2 fw-bolder border-0 btn">Show/Hide Editor Info</div>\n' +
    '<div class="d-none card-body m-0 p-1 px-2">\n' +
    '<p class="card-text small">\n' +
    '<strong>IMPORTANT!</strong> If a custom attribute <strong>data-preserve-source</strong> is found on tags &lt;img&gt;, ' +
    '&lt;script&gt;, &lt;a&gt; and &lt;link&gt;, then the corresponding <strong>src</strong> or <strong>href</strong> attributes are preserved.\n' +
    'The custom attribute <strong>data-preserve-source</strong> must be used for anchors and external links.<br>\n' +
    ' <code>&lt;a data-preserve-source href="#custom-anchor" ...</code><br>\n' +
    'If custom attribute <strong>data-preserve-source</strong> is missing, when showing the contents, the tags ' +
    '&lt;img&gt;, &lt;script&gt;, &lt;a&gt; and &lt;link&gt; will have their ' +
    '<strong>src</strong> or <strong>href</strong> attributes adjusted in order to match the base url of the application.<br>\n' +
    '<code>&lt;a href="contact" ...</code><br>\n' +
    'If <strong>src</strong> or <strong>href</strong> attribute contains the file name of an attachment then ' +
    'the attribute will be adjusted to match the real path of that static file which is attached using the <strong>Attachments</strong> section.<br>\n' +
    '<code>&lt;img src="attachment-name.png" ...</code><br>\n' +
    '</p>\n' +
    '<p class="card-text small">\n' +
    '<strong>Content-Security-Policy - no inline scripts and styles!</strong><br>\n' +
    'Keep scripts and styles in external files. This approach also\n' +
    'satisfies the default Content-Security-Policy of the application.\n' +
    '</p>\n' +
    '</div>\n' +
    '</div>'

  static #htmlTemplateVisualMenuExtra1 = '<li class="nav-item m-0 mb-2 me-2 p-0 vr"></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Toggle highlight sections">&blk14;</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Increase editor height">&plusb;</button></li>\n' +
    '<li class="nav-item m-0 mb-2 me-2 p-0"><button type="button" class="nav-link text-bg-light border fs-5" title="Decrease editor height">&minusb;</button></li>\n'

  /**
   * @member {boolean}
   */
  #hasCss = document.styleSheets.length > 0

  /**
   * @param {HTMLDivElement} parentDiv
   * @param {string} verticalSize Values: "small" (20vh), "medium" (40vh), "large" (60vh), "x-large" (80vh)
   */
  constructor (parentDiv, verticalSize = 'medium') {
    if (!(parentDiv instanceof HTMLDivElement)) {
      throw new TypeError('TextEditor requires parent div element')
    }

    this.#parentDiv = parentDiv

    this.#setupHtml()
    this.#setupCSS()
    this.#setVerticalHeight(verticalSize)
    this.#setupEventListeners()
  }

  get text () {
    // TODO: decide what to do with article tag
    // TODO: handle images
    // TODO: handle href, src etc
    this.#updateText()
    return this.#text
  }

  set text (rawText) {
    if (typeof rawText !== 'string') {
      throw new TypeError('Expecting a string')
    }
    this.#text = rawText
    if (this.visualEditorActive) {
      this.#htmlElements.editor.innerText = ''
      this.#htmlElements.editor.insertAdjacentHTML('afterbegin', rawText)
    } else {
      this.#htmlElements.codeEditor.value = this.text
    }
  }

  /**
   * This method must be used in order to retrieve the actual usable text which
   *  can be stored in database.
   * @returns {string}
   */
  getProductionText () {
    // uses the visual editor which can manipulate HTML elements

    // update contents of the visual editor
    if (!this.visualEditorActive) {
      this.#htmlElements.editor.innerText = ''
      this.#htmlElements.editor.insertAdjacentHTML('afterbegin',
        this.#htmlElements.codeEditor.value)
    }

    // perform replacements
    const imgCollection = this.#htmlElements.editor.getElementsByTagName('img')
    for (let i = 0; i < imgCollection.length; i++) {
      const img = imgCollection.item(i)
      if (img.hasAttribute('data-image-name') &&
        img.src.startsWith('blob:')) {
        img.setAttribute('src',
          img.getAttribute('data-image-name'))
        img.removeAttribute('data-image-name')
      }
    }

    return this.#htmlElements.editor.innerHTML
  }

  static #cssRules = {
    contours: [
      '.cleandeck-evs-contour article, .cleandeck-evs-contour div ' +
      '{ border: 1px solid lightgrey; padding: 3px; }',
      '.cleandeck-evs-contour h1, .cleandeck-evs-contour h2, ' +
      '.cleandeck-evs-contour h3, .cleandeck-evs-contour h4, ' +
      '.cleandeck-evs-contour h5, .cleandeck-evs-contour h6 ' +
      '{ border: 1px dashed blue; padding: 3px; }',
      '.cleandeck-evs-contour p { border: 1px solid lightblue; padding: 3px; }',
      '.cleandeck-evs-contour ul, .cleandeck-evs-contour ol ' +
      '{ border: 1px dashed limegreen; padding: 3px; }',
      '.cleandeck-evs-contour span { border: 1px solid yellow; padding: 3px; }'
    ]
  }

  #setupCSS () {
    if (!this.#hasCss) {
      return
    }

    const css0 = document.styleSheets.item(0)
    css0.insertRule('.cleandeck-evs-20 { height: 20vh; max-height: 20vh; }')
    css0.insertRule('.cleandeck-evs-40 { height: 40vh; max-height: 40vh; }')
    css0.insertRule('.cleandeck-evs-60 { height: 60vh; max-height: 60vh; }')
    css0.insertRule('.cleandeck-evs-80 { height: 80vh; max-height: 80vh; }')

    css0.insertRule('.dropdown-menu-cleandeck-narrow { min-width: 3rem !important; }')
    css0.insertRule('.dropdown-menu-cleandeck-medium { min-width: 8rem !important; }')

    this.#editorOptionToggleHighlight()

    this.#htmlElements.editor.classList.add('cleandeck-evs-contour')
  }

  #setVerticalHeight (verticalSize) {
    if (!this.#hasCss) {
      return
    }

    let verticalHeight
    if (this.#verticalSizes.has(verticalSize)) {
      verticalHeight = this.#verticalSizes.get(verticalSize)
      this.#activeVerticalSize = verticalSize
    } else {
      // defaults to 'medium' (40vh)
      verticalHeight = 40
      this.#activeVerticalSize = 'medium'
    }

    this.#htmlElements.editor.classList.add('cleandeck-evs-' + verticalHeight)
    this.#htmlElements.codeEditor.classList.add('cleandeck-evs-' + verticalHeight)
  }

  #setupHtml () {
    this.#parentDiv.innerHTML = TextEditor.#htmlTemplateVisualMenu

    // main buttons
    const mainUlMenus = this.#parentDiv.getElementsByClassName('nav')
    this.#htmlElements.mainMenu = mainUlMenus.item(0)
    this.#htmlElements.visualEditorOptionsMenu = mainUlMenus.item(1)

    if (this.#hasCss) {
      const tmpHolder = document.createElement('div')
      tmpHolder.innerHTML = TextEditor.#htmlTemplateVisualMenuExtra1
      this.#htmlElements.visualEditorOptionsMenu.append(...tmpHolder.childNodes)
    }

    const mainMenuButtons = this.#htmlElements.mainMenu.getElementsByTagName('button')
    this.#htmlElements.buttonVisual = mainMenuButtons.item(0)
    this.#htmlElements.buttonText = mainMenuButtons.item(1)

    // visual editor buttons
    const visualEditorOptionsMenuButtons =
      this.#htmlElements.visualEditorOptionsMenu.getElementsByTagName('button')
    this.#htmlElements.optionsBold = visualEditorOptionsMenuButtons.item(0)
    this.#htmlElements.optionsItalic = visualEditorOptionsMenuButtons.item(1)
    this.#htmlElements.optionsUnderline = visualEditorOptionsMenuButtons.item(2)
    this.#htmlElements.optionsUnorderedList = visualEditorOptionsMenuButtons.item(3)
    this.#htmlElements.optionsOrderedList = visualEditorOptionsMenuButtons.item(4)
    this.#htmlElements.optionsLink = visualEditorOptionsMenuButtons.item(5)
    this.#htmlElements.optionsParagraph = visualEditorOptionsMenuButtons.item(6)
    this.#htmlElements.optionsImage = visualEditorOptionsMenuButtons.item(7)
    // actions targeting visual editor
    if (this.#hasCss) {
      this.#htmlElements.optionsHighlight = visualEditorOptionsMenuButtons.item(8)
      this.#htmlElements.optionsEditorHeightIncrease = visualEditorOptionsMenuButtons.item(9)
      this.#htmlElements.optionsEditorHeightDecrease = visualEditorOptionsMenuButtons.item(10)
    }
    // dropdowns
    const dropdowns = this.#htmlElements.visualEditorOptionsMenu
      .getElementsByClassName('nav-item dropdown')
    //   - headings dropdown
    this.#htmlElements.dropdownHeadings = dropdowns.item(0)
    const dropdownHeadingsItems =
      this.#htmlElements.dropdownHeadings.getElementsByClassName('dropdown-item')
    this.#htmlElements.optionsHeading1 = dropdownHeadingsItems.item(0)
    this.#htmlElements.optionsHeading2 = dropdownHeadingsItems.item(1)
    this.#htmlElements.optionsHeading3 = dropdownHeadingsItems.item(2)
    this.#htmlElements.optionsHeading4 = dropdownHeadingsItems.item(3)
    this.#htmlElements.optionsHeading5 = dropdownHeadingsItems.item(4)
    this.#htmlElements.optionsHeading6 = dropdownHeadingsItems.item(5)
    //   - html tags dropdown
    this.#htmlElements.dropdownHtmlTags = dropdowns.item(1)
    const dropdownHtmlTagsItems =
      this.#htmlElements.dropdownHtmlTags.getElementsByClassName('dropdown-item')
    this.#htmlElements.optionsHtmlTagDiv = dropdownHtmlTagsItems.item(0)
    this.#htmlElements.optionsHtmlTagSpan = dropdownHtmlTagsItems.item(1)
    // visual editor
    this.#htmlElements.editor =
      this.#parentDiv.getElementsByTagName('article').item(0)
    // code editor
    this.#htmlElements.codeEditor =
      this.#parentDiv.getElementsByTagName('textarea').item(0)

    // dialogs
    const dialogs = this.#parentDiv.getElementsByTagName('dialog')
    // dialog link
    this.#htmlElements.dialogLink = dialogs.item(0)
    this.#htmlElements.dialogLinkInputs = this.#htmlElements.dialogLink
      .getElementsByTagName('input')
    this.#htmlElements.dialogLinkSelects = this.#htmlElements.dialogLink
      .getElementsByTagName('select')
    this.#htmlElements.dialogLinkButtons = this.#htmlElements.dialogLink
      .getElementsByTagName('button')
    this.#htmlElements.dialogLinkButtonCancel = this.#htmlElements.dialogLinkButtons.item(0)
    this.#htmlElements.dialogLinkButtonOk = this.#htmlElements.dialogLinkButtons.item(1)
    // dialog image
    this.#htmlElements.dialogImage = dialogs.item(1)
    this.#htmlElements.dialogImageInputs = this.#htmlElements.dialogImage
      .getElementsByTagName('input')
    this.#htmlElements.dialogImageSelects = this.#htmlElements.dialogImage
      .getElementsByTagName('select')
    this.#htmlElements.dialogImageButtons = this.#htmlElements.dialogImage
      .getElementsByTagName('button')
    this.#htmlElements.dialogImageButtonCancel = this.#htmlElements.dialogImageButtons.item(0)
    this.#htmlElements.dialogImageButtonOk = this.#htmlElements.dialogImageButtons.item(1)
    // editor info
    this.#htmlElements.editorInfoCard =
      this.#parentDiv.getElementsByClassName('card').item(0)
    this.#htmlElements.editorInfoHeader =
      this.#parentDiv.getElementsByClassName('card-header').item(0)
    this.#htmlElements.editorInfoBody =
      this.#parentDiv.getElementsByClassName('card-body').item(0)
  }

  #setupEventListeners () {
    this.#htmlElements.buttonVisual.addEventListener('click',
      this.#buttonVisualOnClick.bind(this))
    this.#htmlElements.buttonText.addEventListener('click',
      this.#buttonTextOnClick.bind(this))
    this.#htmlElements.editor.addEventListener('paste',
      TextEditor.#editorPaste.bind(this))
    // text formatting
    this.#htmlElements.optionsBold.addEventListener('click',
      this.#editorOptionNewElement.bind(this, 'strong'))
    this.#htmlElements.optionsItalic.addEventListener('click',
      this.#editorOptionNewElement.bind(this, 'em'))
    this.#htmlElements.optionsUnderline.addEventListener('click',
      this.#editorOptionNewElement.bind(this, 'span', { class: 'text-decoration-underline' }))
    // elements
    this.#htmlElements.optionsUnorderedList.addEventListener('click',
      this.#editorOptionNewElement.bind(this, 'ul'))
    this.#htmlElements.optionsOrderedList.addEventListener('click',
      this.#editorOptionNewElement.bind(this, 'ol'))
    this.#htmlElements.dropdownHeadings.addEventListener('shown.bs.dropdown',
      function () {
        this.#recordLastRange()
      }.bind(this))
    this.#htmlElements.dropdownHtmlTags.addEventListener('shown.bs.dropdown',
      function () {
        this.#recordLastRange()
      }.bind(this))
    this.#htmlElements.optionsHeading1.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('h1')
      }.bind(this))
    this.#htmlElements.optionsHeading2.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('h2')
      }.bind(this))
    this.#htmlElements.optionsHeading3.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('h3')
      }.bind(this))
    this.#htmlElements.optionsHeading4.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('h4')
      }.bind(this))
    this.#htmlElements.optionsHeading5.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('h5')
      }.bind(this))
    this.#htmlElements.optionsHeading6.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('h6')
      }.bind(this))
    this.#htmlElements.optionsHtmlTagDiv.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('div')
      }.bind(this))
    this.#htmlElements.optionsHtmlTagSpan.addEventListener('click',
      function () {
        this.#resumeAtLastRange()
        this.#editorOptionNewElement('span')
      }.bind(this))
    this.#htmlElements.optionsLink.addEventListener('click',
      function () {
        this.#recordLastRange()
        this.#htmlElements.dialogLink.showModal()
      }.bind(this))
    this.#htmlElements.optionsParagraph.addEventListener('click',
      this.#editorOptionNewElement.bind(this, 'p'))
    this.#htmlElements.optionsImage.addEventListener('click',
      function () {
        this.#recordLastRange()
        this.#htmlElements.dialogImage.showModal()
      }.bind(this))
    // dialogs
    this.#htmlElements.dialogLinkButtonCancel.addEventListener('click',
      this.#dialogLinkClose.bind(this))
    this.#htmlElements.dialogLinkButtonOk.addEventListener('click',
      this.#dialogLinkOk.bind(this))
    this.#htmlElements.dialogImageButtonCancel.addEventListener('click',
      this.#dialogImageClose.bind(this))
    this.#htmlElements.dialogImageButtonOk.addEventListener('click',
      this.#dialogImageOk.bind(this))
    // extra options
    if (this.#htmlElements.optionsHighlight) {
      this.#htmlElements.optionsHighlight.addEventListener('click',
        this.#editorOptionToggleHighlight.bind(this))
    }
    if (this.#htmlElements.optionsEditorHeightIncrease) {
      this.#htmlElements.optionsEditorHeightIncrease.addEventListener('click',
        this.#editorOptionChangeVerticalHeight.bind(this, true))
    }
    if (this.#htmlElements.optionsEditorHeightDecrease) {
      this.#htmlElements.optionsEditorHeightDecrease.addEventListener('click',
        this.#editorOptionChangeVerticalHeight.bind(this, false))
    }
    // editor info
    this.#htmlElements.editorInfoHeader.addEventListener('click',
      function () {
        this.#htmlElements.editorInfoCard.classList.toggle('border-secondary')
        this.#htmlElements.editorInfoCard.classList.toggle('border')
        this.#htmlElements.editorInfoBody.classList.toggle('d-none')
      }.bind(this))
  }

  #recordLastRange () {
    this.#htmlElements.editor.focus()
    const selection = window.getSelection()
    this.#lastRange0 = selection.getRangeAt(0).cloneRange()
  }

  #resumeAtLastRange () {
    this.#htmlElements.editor.focus()
    this.#lastRange0.collapse(true)
    const activeSelection = window.getSelection()
    activeSelection.removeAllRanges()
    activeSelection.addRange(this.#lastRange0)
  }

  #dialogLinkReset () {
    // observe: don't reset last selected options
    this.#htmlElements.dialogLinkInputs.item(0).value = ''
    this.#htmlElements.dialogLinkInputs.item(1).value = ''
  }

  #dialogImageReset () {
    this.#htmlElements.dialogImageInputs.item(0).value = ''
    this.#htmlElements.dialogImageInputs.item(1).value = ''
    this.#htmlElements.dialogImageInputs.item(2).value = ''
    this.#htmlElements.dialogImageInputs.item(3).value = ''
  }

  #dialogLinkOk () {
    // Important. First close the dialog. Do not reset yet.
    this.#htmlElements.dialogLink.close()

    this.#resumeAtLastRange()

    const attributes = {
      target: this.#htmlElements.dialogLinkSelects.item(0).value,
      href: this.#htmlElements.dialogLinkInputs.item(0).value
    }
    // Important
    if (/^http:\/\//i.test(attributes.href)) {
      attributes['data-preserve-source'] = 'true'
    }

    this.#editorOptionNewElement('a',
      attributes,
      this.#htmlElements.dialogLinkInputs.item(1).value
    )

    this.#dialogLinkReset()
  }

  #dialogImageOk () {
    // Important. First close the dialog. Do not reset yet.
    this.#htmlElements.dialogImage.close()
    if (this.#htmlElements.dialogImageInputs.item(0).value.trim() === '' &&
      this.#htmlElements.dialogImageSelects.item(0).value === '') {
      this.#dialogImageReset()
      return
    }

    this.#resumeAtLastRange()

    const attributes = {
      width: Math.round(this.#htmlElements.dialogImageInputs.item(2).value) || 100,
      height: Math.round(this.#htmlElements.dialogImageInputs.item(3).value) || 100
    }
    if (this.#htmlElements.dialogImageInputs.item(1).value !== '') {
      attributes.alt = this.#htmlElements.dialogImageInputs.item(1).value
    }
    if (this.#htmlElements.dialogImageSelects.item(0).selectedIndex) {
      // attached image
      const targetAttachment =
        this.#attachments[this.#htmlElements.dialogImageSelects.item(0).selectedIndex - 1]
      attributes.src = URL.createObjectURL(targetAttachment)
      attributes['data-image-name'] = targetAttachment.name
    } else {
      // not attached image
      attributes.src = this.#htmlElements.dialogImageInputs.item(0).value
    }
    // Important
    if (/^http:\/\//i.test(attributes.src)) {
      attributes['data-preserve-source'] = 'true'
    }

    this.#editorOptionNewElement('img', attributes)

    this.#dialogImageReset()
  }

  #dialogLinkClose () {
    this.#htmlElements.dialogLink.close()
    this.#dialogLinkReset()
  }

  #dialogImageClose () {
    this.#htmlElements.dialogImage.close()
    this.#dialogImageReset()
  }

  #buttonVisualOnClick () {
    if (this.#visualEditorActive) {
      return
    }
    this.#updateText()
    this.#visualEditorActive = true
    this.#htmlElements.editor.innerText = ''
    this.#htmlElements.editor.insertAdjacentHTML('afterbegin', this.#text)
    this.#htmlElements.buttonVisual.classList.toggle('active')
    this.#htmlElements.buttonText.classList.toggle('active')

    this.#htmlElements.codeEditor.classList.add('d-none')
    this.#htmlElements.visualEditorOptionsMenu.classList.remove('d-none')
    this.#htmlElements.editor.classList.remove('d-none')
  }

  #buttonTextOnClick () {
    if (!this.#visualEditorActive) {
      return
    }
    this.#updateText()
    this.#visualEditorActive = false
    this.#htmlElements.codeEditor.textContent = this.#text
    this.#htmlElements.buttonVisual.classList.toggle('active')
    this.#htmlElements.buttonText.classList.toggle('active')

    this.#htmlElements.editor.classList.add('d-none')
    this.#htmlElements.visualEditorOptionsMenu.classList.add('d-none')
    this.#htmlElements.codeEditor.classList.remove('d-none')
  }

  static #insertElementAtCursor (element, isEmptyElement, hasChildren) {
    const selection = window.getSelection()
    if (!selection.rangeCount) {
      return
    }

    const range = selection.getRangeAt(0)
    const rangeCollapsed = range.collapsed
    // get range contents before inserting node
    const rangeContents = range.extractContents()

    range.insertNode(element)

    if (rangeCollapsed) {
      // nothing selected

      // some elements must have something selected
      // HTMLImageElement to be regarded as an empty HTML element.
      // Other empty HTML elements to be handled identically.
      if (isEmptyElement) {
        range.setStartAfter(element)
      } else if (hasChildren) {
        range.setStart(element.childNodes[0], 0)
      } else {
        const emSp = document.createTextNode('\u00A0')
        element.appendChild(emSp)
        range.setStartAfter(emSp)
        range.collapse(true)
      }
    } else {
      // text selected
      if (hasChildren) {
        element.childNodes[0].append(...rangeContents.childNodes)
        range.setStart(element, 1)
      } else {
        element.append(...rangeContents.childNodes)
        range.setStart(element, 1)
      }
    }
    range.collapse(true)
    selection.removeAllRanges()
    selection.addRange(range)
  }

  /**
   * @param {Node} element
   * @param {number} position Use 0 to move cursor to the first child node,
   *   element.childNodes.length to move cursor to the last child node, and
   *   values in-between to focus other child nodes.
   * @private
   */
  static #moveCursorTo (element, position) {
    const range = document.createRange()
    const selection = window.getSelection()
    range.setStart(element, position)
    range.collapse(true)
    selection.removeAllRanges()
    selection.addRange(range)
  }

  static #filterStyleAttribute (subject) {
    return subject.replace(/style="[^"]*"/g, '')
  }

  static #editorPaste (event) {
    event.preventDefault()
    let paste = (event.clipboardData || window.clipboardData)
      .getData('text/html')
    const selection = window.getSelection()
    if (!selection.rangeCount) {
      return
    }

    paste = TextEditor.#filterStyleAttribute(paste)

    selection.deleteFromDocument()

    const wd = document.createElement('div')
    wd.insertAdjacentHTML('afterbegin', paste)
    const df = new DocumentFragment()
    df.append(...wd.childNodes)
    selection.getRangeAt(0).insertNode(df)
    selection.collapseToEnd()
  }

  #updateText () {
    this.#text =
      this.visualEditorActive
        ? this.#htmlElements.editor.innerHTML.replace(/(<\/[^>]+>)/g, '$1\n')
        : this.#htmlElements.codeEditor.value
  }

  /**
   *
   * @param {string} optionType
   * @param {object|null} attributes
   * @param {string|null} value
   */
  #editorOptionNewElement (optionType, attributes = null, value = null) {
    const allowedElements = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6',
      'a', 'div', 'img', 'ul', 'ol', 'p', 'strong', 'em', 'span']
    if (!allowedElements.includes(optionType)) {
      console.error('Unknown option: ' + optionType)
      return
    }

    let hasChildren = false
    const emptyElements = ['img']
    const isEmptyElement = emptyElements.includes(optionType)

    const newElement = document.createElement(optionType)
    if (attributes && !(attributes instanceof Event)) {
      for (const attribute in attributes) {
        if (Object.prototype.hasOwnProperty.call(attributes, attribute)) {
          newElement.setAttribute(attribute, attributes[attribute])
        }
      }
    }
    if (typeof value === 'string') {
      const valueTextNode = document.createTextNode(value)
      newElement.appendChild(valueTextNode)
    }
    switch (optionType) {
      case 'ul':
      case 'ol':
        newElement.appendChild(document.createElement('li'))
        hasChildren = true
        break
    }
    this.#htmlElements.editor.focus()

    TextEditor.#insertElementAtCursor(newElement, isEmptyElement, hasChildren)
  }

  #editorOptionToggleHighlight () {
    if (!this.#hasCss) {
      return
    }

    const css0 = document.styleSheets.item(0)
    const enableEditorContours = !this.#enableEditorContours
    const contours = TextEditor.#cssRules.contours

    if (enableEditorContours) {
      for (const cssRule of contours) {
        css0.insertRule(cssRule)
      }
    } else {
      const cssRulesLength = css0.cssRules.length
      for (let i = 0; i < cssRulesLength; i++) {
        const cssEntry = css0.cssRules.item(i)
        if (cssEntry) {
          if (contours.includes(cssEntry.cssText)) {
            css0.deleteRule(i)
            // Important!
            i--
          }
        }
      }
    }

    this.#htmlElements.optionsHighlight.classList
      .replace(enableEditorContours ? 'text-bg-light' : 'text-bg-primary',
        enableEditorContours ? 'text-bg-primary' : 'text-bg-light')
    this.#enableEditorContours = enableEditorContours
  }

  #editorOptionChangeVerticalHeight (increase) {
    const activeVerticalHeight = this.#verticalSizes.get(this.#activeVerticalSize)
    const nextVerticalHeight = Math.max(20,
      Math.min(80, activeVerticalHeight + (20 * (increase ? 1 : -1))))

    for (const entry of this.#verticalSizes) {
      if (entry[1] === nextVerticalHeight) {
        this.#activeVerticalSize = entry[0]
        this.#htmlElements.editor.classList.replace(
          'cleandeck-evs-' + activeVerticalHeight,
          'cleandeck-evs-' + nextVerticalHeight)
        this.#htmlElements.codeEditor.classList.replace(
          'cleandeck-evs-' + activeVerticalHeight,
          'cleandeck-evs-' + nextVerticalHeight)
        break
      }
    }
  }

  get visualEditorActive () {
    return this.#visualEditorActive
  }

  get attachments () {
    return this.#attachments
  }

  set attachments (attachments) {
    this.#attachments = attachments

    const dialogImageSelectAttachment = this.#htmlElements.dialogImageSelects.item(0)

    // cleanup
    while (dialogImageSelectAttachment.firstChild) {
      dialogImageSelectAttachment.removeChild(dialogImageSelectAttachment.firstChild)
    }

    // re-populate
    if (!attachments.length) {
      return
    }
    dialogImageSelectAttachment.appendChild(document.createElement('option'))
    for (let i = 0; i < attachments.length; i++) {
      const option = document.createElement('option')
      option.textContent = attachments[i].name
      dialogImageSelectAttachment.appendChild(option)
    }
  }
}
