@extends('client.layouts.app')

@section('content')
    <section class="mx-auto max-w-4xl space-y-5">
        <header class="client-card p-5">
            <h1 class="text-2xl font-semibold tracking-tight">Инструкция по работе с программой</h1>
            <p class="mt-2 text-sm text-slate-500">Краткие шаги по основным сценариям для клиентской части.</p>
        </header>

        <article class="client-card p-5">
            <div class="space-y-6 text-sm leading-6 text-slate-700">
                <section>
                    <h2 class="text-base font-semibold text-slate-900">Как добавить новый автомобиль</h2>
                    <ol class="mt-3 list-decimal space-y-2 pl-5">
                        <li>На главном экране откройте раздел <strong>«Автомобили»</strong>.</li>
                        <li>Нажмите кнопку <strong>«Добавить автомобиль»</strong>.</li>
                        <li>Выберите бренд и модель автомобиля.</li>
                        <li>Заполните VIN, дату отправки, год выпуска и цену.</li>
                        <li>При необходимости добавьте фото, видео и документы.</li>
                        <li>Нажмите кнопку <strong>«Добавить»</strong> для сохранения.</li>
                    </ol>
                    <p class="mt-3 text-xs text-slate-500">
                        Если кнопки «Добавить автомобиль» нет, обратитесь к администратору для выдачи прав доступа.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-slate-900">Как найти автомобиль в списке</h2>
                    <ol class="mt-3 list-decimal space-y-2 pl-5">
                        <li>В верхней строке введите VIN в поле <strong>«Поиск по VIN»</strong>.</li>
                        <li>Система автоматически покажет подходящие автомобили.</li>
                        <li>Нажмите на название автомобиля, чтобы открыть карточку и посмотреть детали.</li>
                    </ol>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-slate-900">Если возникла ошибка</h2>
                    <ul class="mt-3 list-disc space-y-2 pl-5">
                        <li>Проверьте, что все обязательные поля заполнены.</li>
                        <li>Проверьте формат VIN и размер загружаемых файлов.</li>
                        <li>Если ошибка повторяется, обратитесь в техподдержку.</li>
                    </ul>
                </section>
            </div>
        </article>
    </section>
@endsection
