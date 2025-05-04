<template>
    <div class="prescription-detail">
      <a-descriptions bordered :column="1" size="middle">
        <a-descriptions-item label="处方编号">{{ prescription.id }}</a-descriptions-item>
        <a-descriptions-item label="开具日期">{{ formatDate(prescription.date) }}</a-descriptions-item>
        <a-descriptions-item label="开具医生">{{ prescription.doctor.name }} {{ prescription.doctor.title }}</a-descriptions-item>
        <a-descriptions-item label="诊断结果">{{ prescription.diagnosis }}</a-descriptions-item>
        <a-descriptions-item label="医嘱备注">{{ prescription.notes || '无' }}</a-descriptions-item>
        <a-descriptions-item label="处方状态">
          <a-tag :color="prescription.status === 'paid' ? 'green' : 'red'">
            {{ prescription.status === 'paid' ? '已缴费' : '未缴费' }}
          </a-tag>
        </a-descriptions-item>
      </a-descriptions>
  
      <div class="drugs-list">
        <h3>药品清单</h3>
        <a-table
          :columns="drugColumns"
          :data-source="prescription.drugs"
          :row-key="record => record.id"
          :pagination="false"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'price'">
              ¥{{ record.price }}
            </template>
            <template v-else-if="column.key === 'total'">
              ¥{{ record.total }}
            </template>
          </template>
          <template #summary>
            <a-table-summary>
              <a-table-summary-row>
                <a-table-summary-cell :index="0" :col-span="4" align="right">
                  合计金额：
                </a-table-summary-cell>
                <a-table-summary-cell :index="1" align="right">
                  ¥{{ prescription.amount.toFixed(2) }}
                </a-table-summary-cell>
              </a-table-summary-row>
            </a-table-summary>
          </template>
        </a-table>
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
  
  const drugColumns = [
    {
      title: '药品名称',
      dataIndex: 'name',
      key: 'name'
    },
    {
      title: '规格',
      dataIndex: 'specification',
      key: 'specification'
    },
    {
      title: '用法用量',
      key: 'usage',
      customRender: ({ record }) => {
        return `${record.dosage}，${record.frequency}`
      }
    },
    {
      title: '数量',
      dataIndex: 'quantity',
      key: 'quantity',
      align: 'right'
    },
    {
      title: '单价(元)',
      key: 'price',
      align: 'right'
    },
    {
      title: '小计(元)',
      key: 'total',
      align: 'right'
    }
  ]
  
  const formatDate = date => {
    return date ? dayjs(date).format('YYYY-MM-DD') : '-'
  }
  </script>
  
  <style scoped>
  .prescription-detail {
    padding: 8px;
  }
  
  .drugs-list {
    margin-top: 24px;
  }
  
  .drugs-list h3 {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  </style>