$(() => {
    const MAX_FIELDS_PER_ROW = 3;
    const FIELD_GRID_SIZE = 12;
    const renderer = new marked.Renderer();
    var embedDataArray = [];

    function parseWebhookData() {
        let el = $('#edit-id');

        if (el.length > 0) {
            let id = el.data('webhookid');

            if (id && typeof webhookData[id] !== undefined) {
                let wbData = webhookData[id];

                embedDataArray.push({
                    containerId: `edit-webhook-${id}`,
                    webhook_url: wbData.webhook_url,
                    webhook_name: wbData.webhook_name,
                    webhook_avatar: wbData.webhook_avatar,
                    content: wbData.content,
                    embeds: JSON.parse(wbData.embeds),
                });

                updatePreview(`edit-webhook-${id}`);
            }
        }
    }

    parseWebhookData();

    $(document).on('click', '[data-saveeventwebhook]', (e) => {
        let el = $(e.currentTarget);
        let containerId = el.data('container-id');
        let embedData = getEmbedDataByContainerId(containerId);

        var result = embedData,
            path = el.data('savepath'),
            method = el.data('savemethod');

        result.event = $('#event_webhook').val();

        sendRequest(result, path, method);
    });

    renderer.text = function (text) {
        return text
            .replace(/\|\|(.+?)\|\|/g, '<span class="highlight">$1</span>')
            .replace(
                /{(.+?)}/g,
                '<span class="highlight-event" data-translate="eventwebhook.replaced_by_event" data-translate-attribute="data-tooltip">$1</span>',
            );
    };

    marked.use({
        renderer,
    });

    function getFieldGridColumn(field, embed) {
        const fieldIndex = embed?.fields.indexOf(field);

        if (!field.inline) return `1 / ${FIELD_GRID_SIZE + 1}`;

        let startingField = fieldIndex;
        while (startingField > 0 && embed?.fields[startingField - 1].inline) {
            startingField -= 1;
        }

        let totalInlineFields = 0;
        while (
            embed?.fields.length > startingField + totalInlineFields &&
            embed?.fields[startingField + totalInlineFields].inline
        ) {
            totalInlineFields += 1;
        }

        const indexInSequence = fieldIndex - startingField;
        const currentRow = Math.floor(indexInSequence / MAX_FIELDS_PER_ROW);
        const indexOnRow = indexInSequence % MAX_FIELDS_PER_ROW;
        const totalOnLastRow =
            totalInlineFields % MAX_FIELDS_PER_ROW || MAX_FIELDS_PER_ROW;
        const fullRows = Math.floor(
            (totalInlineFields - totalOnLastRow) / MAX_FIELDS_PER_ROW,
        );
        const totalOnRow =
            currentRow >= fullRows ? totalOnLastRow : MAX_FIELDS_PER_ROW;

        const columnSpan = FIELD_GRID_SIZE / totalOnRow;
        const start = indexOnRow * columnSpan + 1;
        const end = start + columnSpan;

        return `${start} / ${end}`;
    }

    $(document).on('change', '#event_webhook_select', updateWebhookEventValue);

    $(document).on('input', '#event_webhook_other', function () {
        $('#event_webhook').val($(this).val());
    });

    function updateWebhookEventValue() {
        var selectedValue = $('#event_webhook_select').val();
        if (selectedValue === 'other') {
            $('#event_webhook_other').show().focus();
            $('#event_webhook_other').attr('required', true);
            $('#event').val($('#event_webhook_other').val() || '');
        } else {
            $('#event_webhook_other').hide();
            $('#event_webhook_other').attr('required', false);
            $('#event_webhook').val(selectedValue);
        }
    }

    updateWebhookEventValue();

    document
        .querySelector('.chrome-tabs')
        .addEventListener('contentRender', ({ detail }) => {
            updateWebhookEventValue();
            parseWebhookData();
        });

    $(document).on('click', '.acnav__label', function () {
        var label = $(this);
        var parent = label.parent('.has-children');
        var list = label.siblings('.acnav__list');

        if (parent.hasClass('is-open')) {
            list.slideUp('fast');
            parent.removeClass('is-open');
        } else {
            list.slideDown('fast');
            parent.addClass('is-open');
        }
    });

    $(document).on('input', '.resizable', function (e) {
        while (
            $(this).outerHeight() <
            this.scrollHeight +
                parseFloat($(this).css('borderTopWidth')) +
                parseFloat($(this).css('borderBottomWidth'))
        ) {
            $(this).height($(this).height() + 1);
        }
    });

    function getEmbedDataByContainerId(containerId) {
        let embedData = embedDataArray.find(
            (data) => data.containerId === containerId,
        );
        if (!embedData) {
            embedData = {
                containerId: containerId,
                webhook_url: '',
                webhook_name: '',
                webhook_avatar: '',
                content: '',
                embeds: [],
            };
            embedDataArray.push(embedData);
        }
        return embedData;
    }

    function updateEmbedData(containerId) {
        let embedData = getEmbedDataByContainerId(containerId);
        let container = $(`#${containerId}`);
        embedData.webhook_url = container.find('[name="webhook_url"]').val();
        embedData.webhook_name = container.find('[name="webhook_name"]').val();
        embedData.webhook_avatar = container
            .find('[name="webhook_avatar"]')
            .val();
        embedData.content = container.find('[name="content"]').val();
        embedData.embeds = [];

        container
            .find('.webhook-embeds .acnav__list--level1 > li')
            .each(function (embedIndex) {
                let embed = $(this);
                let embedId = `embed_${embedIndex + 1}`;

                let embedObject = {
                    author: {
                        name: embed?.find('[name="embed_author"]').val(),
                        url: embed?.find('[name="embed_author_url"]').val(),
                        icon: embed?.find('[name="embed_author_icon"]').val(),
                    },
                    body: {
                        title: embed?.find('[name="embed_title"]').val(),
                        description: embed
                            .find('[name="embed_description"]')
                            .val(),
                        url: embed?.find('[name="embed_url"]').val(),
                        color: embed?.find('[name="embed_color"]').val(),
                    },
                    fields: [],
                    footer: {
                        text: embed?.find('[name="embed_footer"]').val(),
                        time: embed?.find('[name="embed_footer_time"]').val(),
                        icon: embed?.find('[name="embed_footer_icon"]').val(),
                    },
                };

                embed
                    .find('.acnav__list--level3 .has-children')
                    .each(function (fieldIndex) {
                        let field = $(this);
                        embedObject.fields.push({
                            inline: field
                                .find('[name="embed_field_inline"]')
                                .is(':checked'),
                            name: field.find('[name="embed_field_name"]').val(),
                            value: field
                                .find('[name="embed_field_value"]')
                                .val(),
                        });
                    });

                embedData.embeds.push(embedObject);
            });

        updatePreview(containerId);
    }

    function updatePreview(containerId) {
        let embedData = getEmbedDataByContainerId(containerId);
        let previewContainer = $(`#${containerId} .webhook-container-preview`);
        console.log(embedData, previewContainer);
        previewContainer.empty(); // Clear previous preview content

        let contentHtml = `
            <div class="webhook-user-content">
                ${
                    embedData.webhook_avatar
                        ? `<img src="${embedData.webhook_avatar}" alt="avatar">`
                        : ''
                }
                <h1>${embedData.webhook_name}</h1>
                <span>BOT</span>
            </div>
            ${
                embedData?.content && embedData?.content.length > 0
                    ? `
            <div class="webhook-content">
                <div>${marked.parse(embedData.content)}</div>
            </div>`
                    : ''
            }
        `;

        previewContainer.append(contentHtml);

        let webhooks = $('<div>').addClass('webhook-embeds-container');

        $.each(embedData.embeds, function (embedIndex, embed) {
            let embedHtml = `
                <div class="webhook-embed-preview" style="border-left: 4px solid ${
                    embed?.body.color
                };">
                    <div class="embed-container">
                        ${
                            embed?.author.icon || embed?.author.name
                                ? '<div class="embed-author">'
                                : ''
                        }
                            ${
                                embed?.author.icon
                                    ? `<img src="${embed?.author.icon}" alt="icon">`
                                    : ''
                            }
                            ${
                                embed?.author.url &&
                                embed?.author.name.length > 0
                                    ? `<a href="${embed?.author.url}" target="_blank">${embed?.author.name}</a>`
                                    : `<span>${embed?.author.name}</span>`
                            }
                        ${
                            embed?.author.icon || embed?.author.name
                                ? '</div>'
                                : ''
                        }
                        ${
                            embed?.body.title
                                ? embed?.body.url
                                    ? `<a href="${embed?.body.url}" target="_blank" class="embed-title link">${embed?.body.title}</a>`
                                    : `<span class="embed-title">${embed?.body.title}</span>`
                                : ''
                        }
                        ${
                            embed?.body.description
                                ? `<div class="embed-body"><div>${marked.parse(
                                      embed?.body.description,
                                  )}</div></div>`
                                : ''
                        }
                        ${
                            embed?.fields && embed?.fields.length > 0
                                ? '<div class="embed-fields">'
                                : ''
                        }
                            ${
                                embed?.fields
                                    ? embed?.fields
                                          .map((field, index, fieldsArray) => {
                                              let gridColumn = '';
                                              if (field.inline) {
                                                  gridColumn =
                                                      getFieldGridColumn(
                                                          field,
                                                          embed,
                                                      );
                                              }
                                              return `
                                    <div class="embed-field" style="${
                                        field.inline
                                            ? `display: inline-block; grid-column: ${gridColumn};`
                                            : 'display: block;'
                                    } margin-right: 10px;">
                                        <div class="embed-field-title">${
                                            field.name
                                        }</div>
                                        <div class="embed-field-content">${marked.parse(
                                            field.value,
                                        )}</div>
                                    </div>
                                `;
                                          })
                                          .join('')
                                    : ''
                            }
                        ${
                            embed?.fields && embed?.fields.length > 0
                                ? '</div>'
                                : ''
                        }
                        ${
                            embed?.footer.text ||
                            embed?.footer.time ||
                            embed?.footer.icon
                                ? '<div class="embed-footer">'
                                : ''
                        }
                            ${
                                embed?.footer.icon
                                    ? `<img src="${embed?.footer.icon}" alt="icon">`
                                    : ''
                            }
                            ${
                                embed?.footer.text || embed?.footer.time
                                    ? `<span>
                                        ${embed?.footer.text}

                                        ${
                                            embed?.footer.time
                                                ? `<div class="embed-footer-dot">•</div><span>${new Date(
                                                      embed?.footer.time,
                                                  ).toLocaleString()}</span>`
                                                : ''
                                        }
                                    </span>`
                                    : ''
                            }
                        ${
                            embed?.footer.text ||
                            embed?.footer.time ||
                            embed?.footer.icon
                                ? '</div>'
                                : ''
                        }
                    </div>
                </div>
            `;
            webhooks.append(embedHtml);
        });

        previewContainer.append(webhooks);
    }

    $(document).on(
        'input change',
        '.webhook-embeds input, .webhook-embeds textarea, [name="webhook_url"], [name="webhook_name"], [name="webhook_avatar"], [name="content"]',
        function () {
            let containerId = $(this)
                .closest('.webhook-editor-container')
                .attr('id');
            updateEmbedData(containerId);
        },
    );

    $(document).on('click', '.webhook-embeds .acnav__btn.mt-2', function () {
        let containerId = $(this)
            .closest('.webhook-editor-container')
            .attr('id');
        let newEmbedIndex =
            $(`#${containerId} .webhook-embeds .acnav__list--level1 > li`)
                .length + 1;
        let newEmbed = `
    <li class="has-children">
        <div class="acnav__label embed_label">
            Embed ${newEmbedIndex}
            <div class="acnav__label-actions">
                <button type="button" class="delete-embed" data-tooltip="${translate(
                    'def.delete',
                )}"><i class="ph-bold ph-x"></i></button>
            </div>
        </div>
        <ul class="acnav__list acnav__list--level2">
            <li class="has-children">
                <div class="acnav__label acnav__label--level2">
                    Author
                </div>
                <ul class="acnav__list acnav__list--level3">
                    <li>
                        <div class="acnav__flex">
                            <div class="webhook-input">
                                <label for="embed_author">${translate(
                                    'eventwebhook.embed_author',
                                )} <i>0/256</i></label>
                                <input type="text" name="embed_author" required maxlength="256" />
                            </div>
                            <div class="webhook-input-group">
                                <div class="webhook-input">
                                    <label for="embed_author_url">${translate(
                                        'eventwebhook.embed_author_url',
                                    )}</label>
                                    <input type="url" name="embed_author_url" />
                                </div>
                                <div class="webhook-input">
                                    <label for="embed_author_icon">${translate(
                                        'eventwebhook.embed_author_icon',
                                    )}</label>
                                    <input type="url" name="embed_author_icon" />
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="has-children">
            <div class="acnav__label acnav__label--level2">
                Body
            </div>
            <ul class="acnav__list acnav__list--level3">
                <div class="acnav__flex">
                    <div class="webhook-input">
                        <label for="embed_title">${translate(
                            'eventwebhook.embed_title',
                        )} <i>0/256</i></label>
                        <textarea rows="1" class="resizable" name="embed_title" maxlength="256"></textarea>
                    </div>
                    <div class="webhook-input">
                        <label for="embed_description">${translate(
                            'eventwebhook.embed_description',
                        )} <i>0/4096</i></label>
                        <textarea class="resizable" name="embed_description" maxlength="4096"></textarea>
                    </div>
                    <div class="webhook-input-group">
                        <div class="webhook-input">
                            <label for="embed_url">${translate(
                                'eventwebhook.embed_url',
                            )}</label>
                            <input type="url" name="embed_url" />
                        </div>
                        <div class="webhook-input">
                            <label for="embed_color">${translate(
                                'eventwebhook.embed_color',
                            )}</label>
                            <input type="color" name="embed_color" value="#202225" required />
                        </div>
                    </div>
                </div>
            </ul>
        </li>
        <li class="has-children">
            <div class="acnav__label acnav__label--level2">
                Fields
            </div>
            <ul class="acnav__list acnav__list--level3">
                <button class="acnav__btn" type="button">${translate(
                    'def.add',
                )}</button>
            </ul>
        </li>
        <li class="has-children">
            <div class="acnav__label acnav__label--level2">
                Footer
            </div>
            <ul class="acnav__list acnav__footer acnav__list--level3">
                <div class="acnav__flex">
                    <div class="webhook-input">
                        <label for="embed_footer">${translate(
                            'eventwebhook.embed_footer',
                        )} <i>0/2048</i></label>
                        <textarea class="resizable" name="embed_footer" maxlength="2048"></textarea>
                    </div>
                    <div class="webhook-input-group">
                        <div class="webhook-input">
                            <label for="embed_footer_time">${translate(
                                'eventwebhook.embed_footer_time',
                            )}</label>
                            <input type="datetime-local" name="embed_footer_time" />
                        </div>
                        <div class="webhook-input">
                            <label for="embed_footer_icon">${translate(
                                'eventwebhook.embed_footer_icon',
                            )}</label>
                            <input type="url" name="embed_footer_icon" />
                        </div>
                    </div>
                </div>
            </ul>
        </li>
    </ul>
</li>`;
        $(`#${containerId} .webhook-embeds .acnav__list--level1`).append(
            newEmbed,
        );
        updateEmbedData(containerId); // Update data after adding a new embed
    });

    $(document).on('click', '.webhook-embeds .delete-embed', function () {
        let containerId = $(this)
            .closest('.webhook-editor-container')
            .attr('id');
        $(this).closest('.has-children').remove();
        updateEmbedData(containerId); // Update data after deleting an embed
    });

    $(document).on(
        'click',
        '.webhook-embeds .acnav__btn:not(.mt-2)',
        function () {
            let containerId = $(this)
                .closest('.webhook-editor-container')
                .attr('id');
            let embedIndex = $(this).closest('li.has-children').index() + 1;
            let newFieldIndex = $(this).siblings('.has-children').length + 1;
            let newField = `
<li class="has-children">
    <div class="acnav__label acnav__label--level3">
        Field ${newFieldIndex}
        <div class="acnav__label-actions">
            <button type="button" class="delete-field" data-tooltip="${translate(
                'def.delete',
            )}"><i class="ph-bold ph-x"></i></button>
        </div>
    </div>
    <ul class="acnav__list acnav__list--level4">
        <li>
            <div class="acnav__flex">
                <div class="form-checkbox webhook-input">
                    <input class="form-check-input" id="embed_field_inline_${embedIndex}_${newFieldIndex}" name="embed_field_inline" type="checkbox">
                    <label class="form-check-label" for="embed_field_inline_${embedIndex}_${newFieldIndex}">
                        ${translate('eventwebhook.embed_field_inline')}
                    </label>
                </div>
                <div class="webhook-input">
                    <label for="embed_field_name">${translate(
                        'eventwebhook.embed_field_name',
                    )} <i>0/256</i></label>
                    <textarea rows="1" class="resizable" name="embed_field_name" maxlength="256"></textarea>
                </div>
                <div class="webhook-input">
                    <label for="embed_field_value">${translate(
                        'eventwebhook.embed_field_value',
                    )} <i>0/1024</i></label>
                    <textarea class="resizable" name="embed_field_value" maxlength="1024"></textarea>
                </div>
            </div>
        </li>
    </ul>
</li>`;
            $(this).before(newField);
            updateEmbedData(containerId); // Update data after adding a new field
        },
    );

    $(document).on('click', '.webhook-embeds .delete-field', function () {
        let containerId = $(this)
            .closest('.webhook-editor-container')
            .attr('id');
        $(this).closest('.has-children').remove();
        updateEmbedData(containerId); // Update data after deleting a field
    });

    // Update character count <i> elements within inputs
    $(document).on(
        'input',
        '.webhook-input input, .webhook-input textarea',
        function () {
            let maxLength = $(this).attr('maxlength');
            let currentLength = $(this).val().length;
            $(this)
                .siblings('label')
                .find('i')
                .text(`${currentLength}/${maxLength}`);
            let containerId = $(this)
                .closest('.webhook-editor-container')
                .attr('id');
            updateEmbedData(containerId); // Update data on input change
        },
    );

    // Markdown shortcuts
    $(document).on(
        'keydown',
        '[name="content"], .webhook-embeds textarea',
        function (e) {
            if (e.ctrlKey || e.metaKey) {
                let textarea = this;
                let value = textarea.value;
                let start = textarea.selectionStart;
                let end = textarea.selectionEnd;
                let selectedText = value.substring(start, end);

                if (e.keyCode === 66) {
                    // Ctrl+B for bold
                    textarea.value =
                        value.substring(0, start) +
                        '**' +
                        selectedText +
                        '**' +
                        value.substring(end);
                    textarea.setSelectionRange(start + 2, end + 2);
                    e.preventDefault();
                    let containerId = $(this)
                        .closest('.webhook-editor-container')
                        .attr('id');
                    updateEmbedData(containerId);
                } else if (e.keyCode === 73) {
                    // Ctrl+I for italic
                    textarea.value =
                        value.substring(0, start) +
                        '*' +
                        selectedText +
                        '*' +
                        value.substring(end);
                    textarea.setSelectionRange(start + 1, end + 1);
                    e.preventDefault();
                    let containerId = $(this)
                        .closest('.webhook-editor-container')
                        .attr('id');
                    updateEmbedData(containerId);
                } else if (e.keyCode === 75) {
                    // Ctrl+K for code block
                    textarea.value =
                        value.substring(0, start) +
                        '`' +
                        selectedText +
                        '`' +
                        value.substring(end);
                    textarea.setSelectionRange(start + 1, end + 1);
                    e.preventDefault();
                    let containerId = $(this)
                        .closest('.webhook-editor-container')
                        .attr('id');
                    updateEmbedData(containerId);
                }
            } else {
                // Если это стрелка вверх или вниз, позвольте событию продолжиться
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                    return;
                }
            }
        },
    );
});
