<template>
  <div class="medical-record-detail">
    <!-- 基本信息 -->
    <a-descriptions bordered :column="2" size="middle">
      <a-descriptions-item label="就诊日期">{{ formatDate(record.date) }}</a-descriptions-item>
      <a-descriptions-item label="就诊科室">{{ record.department }}</a-descriptions-item>
      <a-descriptions-item label="接诊医生">{{ record.doctor.name }} {{ record.doctor.title }}</a-descriptions-item>
      <a-descriptions-item label="就诊状态">
        <a-tag :color="record.status === 'completed' ? 'green' : 'blue'">
          {{ record.status === 'completed' ? '已完成' : '已取消' }}
        </a-tag>
      </a-descriptions-item>
    </a-descriptions>

    <!-- 主诉与病史 -->
    <div class="section">
      <h3 class="section-title">主诉与病史</h3>
      <a-descriptions bordered :column="1" size="middle">
        <a-descriptions-item label="主诉">{{ record.symptoms }}</a-descriptions-item>
        <a-descriptions-item label="现病史">{{ record.diagnosis }}</a-descriptions-item>
        <a-descriptions-item label="既往史">{{ record.medicalHistory }}</a-descriptions-item>
      </a-descriptions>
    </div>

    <!-- 诊断与治疗方案 -->
    <div class="section">
      <h3 class="section-title">诊断与治疗方案</h3>
      <a-descriptions bordered :column="1" size="middle">
        <a-descriptions-item label="初步诊断">{{ record.diagnosis }}</a-descriptions-item>
        <a-descriptions-item label="治疗方案">{{ record.treatmentPlan }}</a-descriptions-item>
      </a-descriptions>
    </div>

    <!-- 处方信息 -->
    <div class="section" v-if="record.prescriptions && record.prescriptions.length > 0">
      <h3 class="section-title">处方信息</h3>
      <div v-for="(prescription, index) in record.prescriptions" :key="prescription.id" class="prescription">
        <a-descriptions bordered :column="1" size="middle">
          <a-descriptions-item label="处方编号">{{ prescription.id }}</a-descriptions-item>
          <a-descriptions-item label="医嘱备注">{{ prescription.notes || '无' }}</a-descriptions-item>
        </a-descriptions>
        
        <a-table
          :columns="drugColumns"
          :data-source="prescription.drugs"
          :row-key="(record, i) => i"
          :pagination="false"
          style="margin-top: 12px;"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'usage'">
              {{ record.dosage }}，{{ record.frequency }}
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- 操作按钮 -->
    <div class="actions">
      <a-button type="primary" @click="downloadRecord">下载完整病历</a-button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import dayjs from 'dayjs'

const props = defineProps({
  record: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['download'])

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
    key: 'usage'
  },
  {
    title: '数量',
    dataIndex: 'quantity',
    key: 'quantity',
    align: 'right'
  }
]

const formatDate = date => {
  return date ? dayjs(date).format('YYYY-MM-DD') : '-'
}

const downloadRecord = () => {
  emit('download', props.record)
}
</script>

<style scoped>
.medical-record-detail {
  padding: 8px;
}

.section {
  margin-top: 24px;
}

.section-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 16px;
  color: rgba(0, 0, 0, 0.85);
}

.prescription {
  margin-bottom: 24px;
}

.actions {
  margin-top: 24px;
  text-align: center;
}
</style>    