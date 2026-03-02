<template>
  <div class="max-w-4xl space-y-6">
    <PageHeader title="Инструкция по работе с программой" />

    <Card>
      <div class="space-y-5 text-sm leading-6 text-gray-700">
        <section>
          <h2 class="text-base font-semibold text-gray-900">Как добавить новый автомобиль</h2>
          <ol class="mt-3 list-decimal space-y-2 pl-5">
            <li>На главном экране откройте раздел <strong>«Автомобили»</strong>.</li>
            <li>Нажмите кнопку <strong>«Добавить автомобиль»</strong>.</li>
            <li>Выберите бренд и модель автомобиля.</li>
            <li>Заполните VIN, дату отправки, год выпуска и цену.</li>
            <li>При необходимости добавьте фото, видео и документы.</li>
            <li>Нажмите кнопку <strong>«Добавить»</strong> для сохранения.</li>
          </ol>
          <p class="mt-3 text-xs text-gray-500">
            Если кнопки «Добавить автомобиль» нет, обратитесь к администратору для выдачи прав доступа.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900">Как найти автомобиль в списке</h2>
          <ol class="mt-3 list-decimal space-y-2 pl-5">
            <li>В верхней строке введите VIN в поле <strong>«Поиск по VIN»</strong>.</li>
            <li>Программа автоматически покажет подходящие автомобили.</li>
            <li>Нажмите на название автомобиля, чтобы открыть карточку и посмотреть детали.</li>
          </ol>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900">Если возникла ошибка</h2>
          <ul class="mt-3 list-disc space-y-2 pl-5">
            <li>Проверьте, что все обязательные поля заполнены.</li>
            <li>Проверьте формат VIN и размер загружаемых файлов.</li>
            <li>Если ошибка повторяется, обратитесь в техподдержку.</li>
          </ul>
        </section>
      </div>
    </Card>
  </div>
</template>

<script setup>
import ClientLayout from '../../Layouts/ClientLayout.vue'
import Card from '../../Components/Card.vue'
import PageHeader from '../../Components/PageHeader.vue'

defineOptions({ layout: ClientLayout })
</script>
