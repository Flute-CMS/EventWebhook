@extends('Core.Admin.Http.Views.layout', [
    'title' => __('admin.title', [
        'name' => __('eventwebhook.admin.edit_title', [
            ':id' => $webhook->id,
        ]),
    ]),
])

@push('header')
    @at(mm('EventWebhook', 'Resources/assets/styles/add.scss'))
    <link rel='stylesheet' href='@asset('whitney')' type='text/css'>
@endpush

@push('content')
    <div class="admin-header d-flex justify-content-between align-items-center" id="edit-id"
        data-webhookid="{{ $webhook->id }}">
        <div>
            <a class="back-btn" href="{{ url('admin/event_webhook/list') }}">
                <i class="ph ph-arrow-left ignore"></i>
                @t('def.back')
            </a>
            <h2>@t('eventwebhook.admin.edit_title', [
                ':id' => $webhook->id,
            ])</h2>
            <p>@t('eventwebhook.admin.edit_description')</p>
        </div>
        <button type="submit" data-saveeventwebhook data-container-id="edit-webhook-{{ $webhook->id }}"
            data-savepath="admin/api/event_webhook/{{ $webhook->id }}" data-savemethod="PUT"
            class="btn size-s btn--with-icon primary">
            @t('def.save')
            <span class="btn__icon arrow"><i class="ph ph-arrow-right"></i></span>
        </button>
    </div>

    <form class="webhook-editor-container" id="edit-webhook-{{ $webhook->id }}">
        <div class="position-relative row form-group">
            <div class="col-sm-3 col-form-label required">
                <label for="event_webhook_select">@t('admin.notifications.event')</label>
            </div>
            <div class="col-sm-9">
                <select name="event_webhook_select" id="event_webhook_select" class="form-control">
                    @foreach ($events as $key => $event)
                        <option value="{{ $key }}" @if ($webhook->event == $key) selected @endif>
                            {{ __("admin.notifications.$event") }}
                        </option>
                    @endforeach
                    <option value="other" @if (!array_key_exists($webhook->event, $events)) selected @endif>@t('admin.notifications.other')</option>
                </select>

                <input id="event_webhook_other" placeholder="@t('admin.notifications.specify_event')" type="text" class="form-control mt-2"
                    @if (!array_key_exists($webhook->event, $events)) style="display:block;" 
                    value="{{ $webhook->event }}" 
                @else 
                    hidden @endif>

                <input type="hidden" name="event_webhook" id="event_webhook" value="{{ $webhook->event }}">
            </div>
        </div>

        <div class="form-group webhook-container">
            <div class="webhook-container-editor">
                <div class="webhook-input">
                    <label for="webhook_url">Webhook URL</label>
                    <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..."
                        value="{{ $webhook->webhook_url }}" required />
                </div>
                <div class="webhook-input">
                    <label for="webhook_name">@t('eventwebhook.username') <i>{{ mb_strlen($webhook->webhook_name) }}/80</i></label>
                    <input type="text" name="webhook_name" value="{{ $webhook->webhook_name }}" required
                        maxlength="80" />
                </div>
                <div class="webhook-input">
                    <label for="webhook_avatar">@t('eventwebhook.avatar')</label>
                    <input type="url" name="webhook_avatar" value="{{ $webhook->webhook_avatar }}" required />
                </div>
                <hr />
                <div class="webhook-input">
                    <label for="content">@t('eventwebhook.content') <i>{{ mb_strlen($webhook->content) }}/2000</i></label>
                    <textarea class="resizable"name="content" id="content" maxlength="2000">{!! $webhook->content !!}</textarea>
                </div>
                <div class="webhook-embeds">
                    <section class="nav-wrap">
                        <div class="acnav">
                            <ul class="acnav__list acnav__list--level1">
                                @foreach (json_decode($webhook->embeds) as $embedIndex => $embed)
                                    <li class="has-children">
                                        <div class="acnav__label embed_label">
                                            Embed {{ $embedIndex + 1 }}
                                            <div class="acnav__label-actions">
                                                <button type="button" class="delete-embed"
                                                    data-tooltip="@t('def.delete')"><i class="ph-bold ph-x"></i></button>
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
                                                                <label for="embed_author">@t('eventwebhook.embed_author')
                                                                    <i>{{ mb_strlen($embed->author->name) }}/256</i></label>
                                                                <input type="text" name="embed_author"
                                                                    value="{{ $embed->author->name }}" maxlength="256" />
                                                            </div>
                                                            <div class="webhook-input-group">
                                                                <div class="webhook-input">
                                                                    <label for="embed_author_url">@t('eventwebhook.embed_author_url')</label>
                                                                    <input type="url" name="embed_author_url"
                                                                        value="{{ $embed->author->url }}" />
                                                                </div>
                                                                <div class="webhook-input">
                                                                    <label
                                                                        for="embed_author_icon">@t('eventwebhook.embed_author_icon')</label>
                                                                    <input type="url" name="embed_author_icon"
                                                                        value="{{ $embed->author->icon }}" />
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
                                                            <label for="embed_title">@t('eventwebhook.embed_title')
                                                                <i>{{ mb_strlen($embed->body->title) }}/256</i></label>
                                                            <textarea rows="1" class="resizable" name="embed_title" maxlength="256">{{ $embed->body->title }}</textarea>
                                                        </div>
                                                        <div class="webhook-input">
                                                            <label for="embed_description">@t('eventwebhook.embed_description')
                                                                <i>{{ mb_strlen($embed->body->description) }}/4096</i></label>
                                                            <textarea class="resizable" name="embed_description" maxlength="4096">{{ $embed->body->description }}</textarea>
                                                        </div>
                                                        <div class="webhook-input-group">
                                                            <div class="webhook-input">
                                                                <label for="embed_url">@t('eventwebhook.embed_url')</label>
                                                                <input type="url" name="embed_url"
                                                                    value="{{ $embed->body->url }}" />
                                                            </div>
                                                            <div class="webhook-input">
                                                                <label for="embed_color">@t('eventwebhook.embed_color')</label>
                                                                <input type="color" name="embed_color"
                                                                    value="{{ $embed->body->color }}" required />
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
                                                    @if (isset($embed->fields))
                                                        @foreach ($embed->fields as $fieldIndex => $field)
                                                            <li class="has-children">
                                                                <div class="acnav__label acnav__label--level3">
                                                                    Field {{ $fieldIndex + 1 }}
                                                                    <div class="acnav__label-actions">
                                                                        <button type="button" class="delete-field"
                                                                            data-tooltip="@t('def.delete')"><i
                                                                                class="ph-bold ph-x"></i></button>
                                                                    </div>
                                                                </div>
                                                                <ul class="acnav__list acnav__list--level4">
                                                                    <li>
                                                                        <div class="acnav__flex">
                                                                            <div class="form-checkbox webhook-input">
                                                                                <input class="form-check-input"
                                                                                    id="embed_field_inline_{{ $embedIndex }}_{{ $fieldIndex }}"
                                                                                    name="embed_field_inline"
                                                                                    type="checkbox"
                                                                                    {{ $field->inline ? 'checked' : '' }}>
                                                                                <label class="form-check-label"
                                                                                    for="embed_field_inline_{{ $embedIndex }}_{{ $fieldIndex }}">
                                                                                    @t('eventwebhook.embed_field_inline')
                                                                                </label>
                                                                            </div>
                                                                            <div class="webhook-input">
                                                                                <label
                                                                                    for="embed_field_name">@t('eventwebhook.embed_field_name')
                                                                                    <i>{{ mb_strlen($field->name) }}/256</i></label>
                                                                                <textarea rows="1" class="resizable" name="embed_field_name" maxlength="256">{{ $field->name }}</textarea>
                                                                            </div>
                                                                            <div class="webhook-input">
                                                                                <label
                                                                                    for="embed_field_value">@t('eventwebhook.embed_field_value')
                                                                                    <i>{{ mb_strlen($field->value) }}/1024</i></label>
                                                                                <textarea class="resizable" name="embed_field_value" maxlength="1024">{{ $field->value }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                    <button class="acnav__btn" type="button">@t('def.add')</button>
                                                </ul>
                                            </li>
                                            <li class="has-children">
                                                <div class="acnav__label acnav__label--level2">
                                                    Footer
                                                </div>
                                                <ul class="acnav__list acnav__footer acnav__list--level3">
                                                    <div class="acnav__flex">
                                                        <div class="webhook-input">
                                                            <label for="embed_footer">@t('eventwebhook.embed_footer')
                                                                <i>{{ mb_strlen($embed->footer->text) }}/2048</i></label>
                                                            <textarea class="resizable" name="embed_footer" maxlength="2048">{{ $embed->footer->text }}</textarea>
                                                        </div>
                                                        <div class="webhook-input-group">
                                                            <div class="webhook-input">
                                                                <label for="embed_footer_time">@t('eventwebhook.embed_footer_time')</label>
                                                                <input type="datetime-local" name="embed_footer_time"
                                                                    value="{{ $embed->footer->time }}" />
                                                            </div>
                                                            <div class="webhook-input">
                                                                <label for="embed_footer_icon">@t('eventwebhook.embed_footer_icon')</label>
                                                                <input type="url" name="embed_footer_icon"
                                                                    value="{{ $embed->footer->icon }}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <button class="acnav__btn wmargin mt-2" type="button">@t('def.add')</button>
                    </section>
                </div>
            </div>
            <div class="webhook-container-preview"></div>
        </div>
    </form>
@endpush

@push('footer')
    <script>
        if (typeof webhookData === 'undefined') {
            var webhookData = [];
        }

        webhookData[{{ $webhook->id }}] = {!! json_encode($webhook) !!};
    </script>
    @at('https://cdn.jsdelivr.net/npm/marked/marked.min.js')
    @at(mm('EventWebhook', 'Resources/assets/js/add.js'))
@endpush
