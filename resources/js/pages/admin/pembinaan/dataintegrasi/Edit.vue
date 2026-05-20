<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  dataintegrasi: any,
  upts: any[]
}>()

const form = useForm({
  _method: 'put',

  upt_id: String(props.dataintegrasi.upt_id),

  tanggal_input:
    props.dataintegrasi.tanggal_input || '',

  tanggal_pelaksanaan_pb:
    props.dataintegrasi.tanggal_pelaksanaan_pb || '',

  jumlah_pb:
    props.dataintegrasi.jumlah_pb || '',

  sk_pb: null as File | null,

  dokumentasi_pb: [] as File[],

  tanggal_pelaksanaan_cb:
    props.dataintegrasi.tanggal_pelaksanaan_cb || '',

  jumlah_cb:
    props.dataintegrasi.jumlah_cb || '',

  sk_cb: null as File | null,

  dokumentasi_cb: [] as File[],

  tanggal_pelaksanaan_cmb:
    props.dataintegrasi.tanggal_pelaksanaan_cmb || '',

  jumlah_cmb:
    props.dataintegrasi.jumlah_cmb || '',

  sk_cmb: null as File | null,

  dokumentasi_cmb: [] as File[],

  tanggal_pelaksanaan_asimilasi:
    props.dataintegrasi.tanggal_pelaksanaan_asimilasi || '',

  jumlah_asimilasi:
    props.dataintegrasi.jumlah_asimilasi || '',

  sk_asimilasi: null as File | null,

  dokumentasi_asimilasi: [] as File[],
})

function handleMultipleFiles(event: Event, key: string) {
  const target = event.target as HTMLInputElement

  if (target.files) {
    form[`dokumentasi_${key}`] =
      Array.from(target.files)
  }
}

function submit() {
  form.post(
    `/admin/data-integrasis/${props.dataintegrasi.id}`,
    {
      forceFormData: true,
    }
  )
}
</script>