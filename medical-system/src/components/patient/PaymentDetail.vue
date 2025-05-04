<template>
    <div class="payment-detail">
      <a-descriptions bordered :column="1" size="middle">
        <a-descriptions-item label="缴费单号">{{ payment.id }}</a-descriptions-item>
        <a-descriptions-item label="交易单号">{{ payment.transactionId }}</a-descriptions-item>
        <a-descriptions-item label="支付时间">{{ formatDateTime(payment.date) }}</a-descriptions-item>
        <a-descriptions-item label="支付方式">{{ getPaymentMethodText(payment.method) }}</a-descriptions-item>
        <a-descriptions-item label="支付金额">
          ¥{{ payment.amount.toFixed(2) }}
        </a-descriptions-item>
        <a-descriptions-item label="支付状态">
          <a-tag :color="payment.status === 'success' ? 'green' : 'red'">
            {{ payment.status === 'success' ? '支付成功' : '支付失败' }}
          </a-tag>
        </a-descriptions-item>
      </a-descriptions>
  
      <div class="prescriptions-list">
        <h3>关联处方</h3>
        <a-table
          :columns="prescriptionColumns"
          :data-source="payment.prescriptions"
          :row-key="record => record.id"
          :pagination="false"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'date'">
              {{ formatDate(record.date) }}
            </template>
            <template v-else-if="column.key === 'doctor'">
              {{ record.doctor.name }} {{ record.doctor.title }}
            </template>
            <template v-else-if="column.key === 'amount'">
              ¥{{ record.amount.toFixed(2) }}
            </template>
            <template v-else-if="column.key === 'action'">
              <a-button type="link" size="small" @click="viewPrescription(record)">
                查看详情
              </a-button>
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import dayjs from 'dayjs'
  
  const props = defineProps({
    payment: {
      type: Object,
      required: true
    }
  })
  
  const emit = defineEmits(['viewPrescription'])
  
  const prescriptionColumns = [
    {
      title: '处方编号',
      dataIndex: 'id',
      key: 'id'
    },
    {
      title: '开具日期',
      dataIndex: 'date',
      key: 'date'
    },
    {
      title: '开具医生',
      dataIndex: 'doctor',
      key: 'doctor'
    },
    {
      title: '金额(元)',
      dataIndex: 'amount',
      key: 'amount',
      align: 'right'
    },
    {
      title: '操作',
      key: 'action',
      width: 100
    }
  ]
  
  const formatDate = date => {
    return date ? dayjs(date).format('YYYY-MM-DD') : '-'
  }
  
  const formatDateTime = datetime => {
    return datetime ? dayjs(datetime).format('YYYY-MM-DD HH:mm:ss') : '-'
  }
  
  const getPaymentMethodText = method => {
    const map = {
      wechat: '微信支付',
      alipay: '支付宝',
      bank: '银行卡'
    }
    return map[method] || method
  }
  
  const viewPrescription = (prescription) => {
    emit('viewPrescription', prescription)
  }
  </script>
  
  <style scoped>
  .payment-detail {
    padding: 8px;
  }
  
  .prescriptions-list {
    margin-top: 24px;
  }
  
  .prescriptions-list h3 {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  </style>