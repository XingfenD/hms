<template>
    <div class="prescription-detail">
      <!-- 处方头部信息 -->
      <div class="prescription-header">
        <div class="hospital-name">XX医院电子处方笺</div>
        <div class="prescription-no">处方编号: {{ prescription.prescriptionNo }}</div>
      </div>
  
      <!-- 患者信息 -->
      <a-descriptions bordered :column="2" size="middle">
        <a-descriptions-item label="患者姓名">{{ prescription.patient.name }}</a-descriptions-item>
        <a-descriptions-item label="性别">{{ prescription.patient.gender === 'male' ? '男' : '女' }}</a-descriptions-item>
        <a-descriptions-item label="年龄">{{ prescription.patient.age }}岁</a-descriptions-item>
        <a-descriptions-item label="病历号">{{ prescription.patient.medicalRecordNo }}</a-descriptions-item>
        <a-descriptions-item label="处方状态">
          <a-tag :color="getStatusColor(prescription.status)">
            {{ getStatusText(prescription.status) }}
          </a-tag>
        </a-descriptions-item>
        <a-descriptions-item label="开具医生">{{ prescription.doctor.name }} {{ prescription.doctor.title }}</a-descriptions-item>
        <a-descriptions-item label="开具日期">{{ formatDate(prescription.date) }}</a-descriptions-item>
        <a-descriptions-item label="作废原因" v-if="prescription.status === 'invalid'">
          {{ prescription.invalidateReason }}
        </a-descriptions-item>
      </a-descriptions>
  
      <!-- 诊断与医嘱 -->
      <div class="diagnosis-section">
        <h3>诊断与医嘱</h3>
        <a-descriptions bordered :column="1" size="middle">
          <a-descriptions-item label="诊断结果">{{ prescription.diagnosis }}</a-descriptions-item>
          <a-descriptions-item label="医嘱备注">{{ prescription.notes || '无' }}</a-descriptions-item>
        </a-descriptions>
      </div>
  
      <!-- 药品清单 -->
      <div class="drugs-section">
        <h3>药品清单</h3>
        <a-table
          :columns="drugColumns"
          :data-source="prescription.drugs"
          :row-key="(record, index) => index"
          :pagination="false"
          bordered
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'drugInfo'">
              <div>{{ record.name }}</div>
              <div class="spec">{{ record.specification }}</div>
            </template>
            <template v-else-if="column.key === 'usage'">
              {{ record.dosage }}，{{ record.frequency }}，{{ record.days }}天
            </template>
            <template v-else-if="column.key === 'total'">
              {{ (record.price * record.quantity).toFixed(2) }}元
            </template>
          </template>
          <template #summary>
            <a-table-summary>
              <a-table-summary-row>
                <a-table-summary-cell :index="0" :col-span="3" align="right">
                  合计金额:
                </a-table-summary-cell>
                <a-table-summary-cell :index="1" align="right">
                  {{ calculateTotalAmount().toFixed(2) }}元
                </a-table-summary-cell>
              </a-table-summary-row>
            </a-table-summary>
          </template>
        </a-table>
      </div>
  
      <!-- 操作按钮 -->
      <div class="actions" v-if="prescription.status === 'valid'">
        <a-button type="primary" danger @click="emitInvalidate">作废处方</a-button>
        <a-button type="primary" @click="printPrescription">打印处方</a-button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import dayjs from 'dayjs'
  
  const props = defineProps({
    prescription: {
      type: Object,
      required: true
    }
  })
  
  const emit = defineEmits(['invalidate', 'print'])
  
  const drugColumns = [
    {
      title: '药品信息',
      key: 'drugInfo',
      width: '40%'
    },
    {
      title: '用法用量',
      key: 'usage',
      width: '30%'
    },
    {
      title: '数量',
      dataIndex: 'quantity',
      key: 'quantity',
      align: 'center',
      width: '10%'
    },
    {
      title: '单价(元)',
      dataIndex: 'price',
      key: 'price',
      align: 'right',
      width: '10%'
    },
    {
      title: '小计(元)',
      key: 'total',
      align: 'right',
      width: '10%'
    }
  ]
  
  const formatDate = date => {
    return date ? dayjs(date).format('YYYY-MM-DD') : '-'
  }
  
  const getStatusText = status => {
    const map = {
      'valid': '有效',
      'expired': '已过期',
      'invalid': '已作废',
      'dispensed': '已取药'
    }
    return map[status] || status
  }
  
  const getStatusColor = status => {
    const map = {
      'valid': 'green',
      'expired': 'orange',
      'invalid': 'red',
      'dispensed': 'blue'
    }
    return map[status] || 'default'
  }
  
  const calculateTotalAmount = () => {
    return props.prescription.drugs.reduce((sum, drug) => {
      return sum + (drug.price * drug.quantity)
    }, 0)
  }
  
  const emitInvalidate = () => {
    emit('invalidate', props.prescription)
  }
  
  const printPrescription = () => {
    emit('print', props.prescription)
  }
  </script>
  
  <style scoped>
  .prescription-detail {
    padding: 8px;
  }
  
  .prescription-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
  }
  
  .hospital-name {
    font-size: 18px;
    font-weight: bold;
  }
  
  .prescription-no {
    color: #666;
  }
  
  .diagnosis-section, .drugs-section {
    margin-top: 24px;
  }
  
  .diagnosis-section h3, .drugs-section h3 {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  
  .spec {
    font-size: 12px;
    color: #666;
  }
  
  .actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
    gap: 16px;
  }
  </style>