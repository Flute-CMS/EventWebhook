@extends('Core.Admin.Http.Views.layout', [
    'title' => __('admin.title', ['name' => __('eventwebhook.admin.add_title')]),
])

@push('header')
    @at(mm('EventWebhook', 'Resources/assets/styles/add.scss'))
    <link rel='stylesheet' href='@asset('whitney')' type='text/css'>
@endpush

@push('content')
    <div class="admin-header d-flex justify-content-between align-items-center">
        <div>
            <a class="back-btn" href="{{ url('admin/event_webhook/list') }}">
                <i class="ph ph-arrow-left ignore"></i>
                @t('def.back')
            </a>
            <h2>@t('eventwebhook.admin.add_title')</h2>
            <p>@t('eventwebhook.admin.add_description')</p>
        </div>
        <button type="submit" data-saveeventwebhook data-container-id="add-webhook" data-savepath="admin/api/event_webhook/add" data-savemethod="POST"
            class="btn size-s btn--with-icon primary">
            @t('def.save')
            <span class="btn__icon arrow"><i class="ph ph-arrow-right"></i></span>
        </button>
    </div>

    <form data-eventwebhook="add" class="webhook-editor-container" id="add-webhook">
        <div class="position-relative row form-group">
            <div class="col-sm-3 col-form-label required">
                <label for="event_webhook_select">@t('admin.notifications.event')</label>
            </div>
            <div class="col-sm-9">
                <select name="event_webhook_select" id="event_webhook_select" class="form-control">
                    @foreach ($events as $key => $event)
                        <option value="{{ $key }}">{{ __("admin.notifications.$event") }}</option>
                    @endforeach
                    <option value="other">@t('admin.notifications.other')</option>
                </select>

                <input id="event_webhook_other" placeholder="@t('admin.notifications.specify_event')" type="text" class="form-control mt-2"
                    hidden>

                <input type="hidden" name="event_webhook" id="event_webhook" value="">
            </div>
        </div>

        <div class="form-group webhook-container">
            <div class="webhook-container-editor">
                <div class="webhook-input">
                    <label for="webhook_url">Webhook URL</label>
                    <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..." required />
                </div>
                <div class="webhook-input">
                    <label for="webhook_name">@t('eventwebhook.username') <i>0/80</i></label>
                    <input type="text" name="webhook_name" value="{{ config('app.name') }}" required maxlength="80" />
                </div>
                <div class="webhook-input">
                    <label for="webhook_avatar">@t('eventwebhook.avatar')</label>
                    <input type="url" name="webhook_avatar" value="{{ url(config('app.logo')) }}" required />
                </div>
                <hr />
                <div class="webhook-input">
                    <label for="content">@t('eventwebhook.content') <i>0/2000</i></label>
                    <textarea class="resizable"name="content" id="content" maxlength="2000"></textarea>
                </div>
                <div class="webhook-embeds">
                    <section class="nav-wrap">
                        <div class="acnav">
                            <ul class="acnav__list acnav__list--level1">
                            </ul>
                        </div>
                        <button class="acnav__btn wmargin mt-2" type="button">@t('def.add')</button>
                    </section>
                </div>
            </div>
            <div class="webhook-container-preview">
                <div class="webhook-user-content">
                    <img src="{{ url(config('app.logo')) }}" alt="avatar">
                    <h1>{{ config('app.name') }}</h1>
                    <span>BOT</span>
                </div>
                <div class="webhook-content">
                    <p>Hello <b>guys!</b></p>
                </div>
            </div>
        </div>
    </form>
@endpush

@push('footer')
    @at('https://cdn.jsdelivr.net/npm/marked/marked.min.js')
    @at(mm('EventWebhook', 'Resources/assets/js/add.js'))
@endpush
