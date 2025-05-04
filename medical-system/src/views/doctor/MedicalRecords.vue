<template>
  <div class="prescription-management-container">
    <a-card title="处方管理" :bordered="false">
      <!-- 筛选区域 -->
      <div class="filter-container">
        <a-form layout="inline" :model="filterForm">
          <a-form-item label="处方状态">
            <a-select
              v-model:value="filterForm.status"
              style="width: 120px"
              placeholder="全部状态"
              allow-clear
            >
              <a-select-option value="valid">有效</a-select-option>
              <a-select-option value="expired">已过期</a-select-option>
              <a-select-option value="invalid">已作废</a-select-option>
              <a-select-option value="dispensed">已取药</a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="患者姓名">
            <a-input
              v-model:value="filterForm.patientName"
              placeholder="输入患者姓名"
              style="width: 150px"
              allow-clear
            />
          </a-form-item>
          <a-form-item label="开具日期">
            <a-range-picker
              v-model:value="filterForm.dateRange"
              style="width: 240px"
            />
          </a-form-item>
          <a-form-item>
            <a-button type="primary" @click="handleSearch">
              <template #icon><SearchOutlined /></template>
              查询
            </a-button>
            <a-button style="margin-left: 8px" @click="resetSearch">
              <template #icon><RedoOutlined /></template>
              重置
            </a-button>
          </a-form-item>
        </a-form>
      </div>

      <!-- 处方列表 -->
      <a-table
        :columns="columns"
        :data-source="prescriptions"
        :row-key="record => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'prescriptionNo'">
            <a @click="viewPrescriptionDetail(record)">{{ record.prescriptionNo }}</a>
          </template>
          <template v-else-if="column.key === 'patient'">
            {{ record.patient.name }} ({{ record.patient.gender === 'male' ? '男' : '女' }} {{ record.patient.age }}岁)
          </template>
          <template v-else-if="column.key === 'date'">
            {{ formatDate(record.date) }}
          </template>
          <template v-else-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ getStatusText(record.status) }}
            </a-tag>
          </template>
          <template v-else-if="column.key === 'amount'">
            ¥{{ record.amount.toFixed(2) }}
          </template>
          <template v-else-if="column.key === 'action'">
            <a-space>
              <a-button size="small" @click="viewPrescriptionDetail(record)">
                详情
              </a-button>
              <a-button
                size="small"
                danger
                @click="showInvalidateModal(record)"
                :disabled="record.status !== 'valid'"
              >
                作废
              </a-button>
              <a-button
                size="small"
                type="primary"
                @click="printPrescription(record)"
              >
                打印
              </a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>

    <!-- 处方详情模态框 -->
    <a-modal
      v-model:visible="detailVisible"
      :title="currentPrescription ? `处方详情 - ${currentPrescription.prescriptionNo}` : ''"
      width="800px"
      :footer="null"
    >
      <div v-if="currentPrescription && appointInfo">
        <h3>患者主诉: {{ appointInfo.pat_requirements }}</h3>
        <h3>过往病史: {{ appointInfo.pat_history }}</h3>
        <h3>初步诊断: {{ appointInfo.diagnosis || '暂无' }}</h3>
        <PrescriptionDetail :prescription="currentPrescription" />
      </div>
    </a-modal>

    <!-- 作废处方模态框 -->
    <a-modal
      v-model:visible="invalidateVisible"
      title="作废处方"
      ok-text="确认作废"
      cancel-text="取消"
      @ok="handleInvalidatePrescription"
    >
      <a-form layout="vertical">
        <a-form-item label="作废原因" required>
          <a-textarea
            v-model:value="invalidateReason"
            placeholder="请输入作废原因"
            :rows="4"
            :maxlength="200"
            show-count
          />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'
import { SearchOutlined, RedoOutlined } from '@ant-design/icons-vue'
import PrescriptionDetail from '../../components/doctor/PrescriptionDetail.vue'
import { getAppoint, getLoginInfo, getPrescriptions, getAppointinfo, get_single_userinfo_data, getPresResult, getDrugs } from '@/api/api.js'

// 表格列定义
const columns = [
  {
    title: '处方编号',
    dataIndex: 'prescriptionNo',
    key: 'prescriptionNo',
    width: 150
  },
  {
    title: '患者信息',
    key: 'patient',
    width: 180
  },
  {
    title: '诊断结果',
    dataIndex: 'diagnosis',
    key: 'diagnosis',
    ellipsis: true
  },
  {
    title: '开具日期',
    dataIndex: 'date',
    key: 'date',
    width: 120,
    sorter: true
  },
  {
    title: '处方状态',
    dataIndex: 'status',
    key: 'status',
    width: 100
  },
  {
    title: '金额(元)',
    dataIndex: 'amount',
    key: 'amount',
    width: 120,
    align: 'right'
  },
  {
    title: '操作',
    key: 'action',
    width: 200
  }
]

// 分页配置
const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  pageSizeOptions: ['10', '20', '50'],
  showTotal: total => `共 ${total} 条`
})

// 筛选表单
const filterForm = ref({
  status: undefined,
  patientName: '',
  dateRange: []
})

// 数据状态
const prescriptions = ref([])
const loading = ref(false)
const detailVisible = ref(false)
const invalidateVisible = ref(false)
const currentPrescription = ref(null)
const invalidateReason = ref('')
const appointInfo = ref(null)

// 初始化加载数据
onMounted(async () => {
  try {
    const loginInfoResponse = await getLoginInfo()
    if (loginInfoResponse.data.code === 200) {
      const doctorId = loginInfoResponse.data.data.doctor_id
      await fetchPrescriptions(doctorId)
    } else {
      message.error('获取登录信息失败')
    }
  } catch (error) {
    message.error('初始化失败: ' + error.message)
  }
})

// 获取处方列表
const fetchPrescriptions = async (doctorId) => {
  try {
    loading.value = true
    const appointResponse = await getAppoint({ doctor_id: doctorId })
    if (appointResponse.data.code === 200) {
      const prescriptionResponse = await getPrescriptions()
      if (prescriptionResponse.data.code === 200) {
        const presData = prescriptionResponse.data.data
        const processedPrescriptions = await Promise.all(presData.map(async (pres) => {
          const apId = pres.ap_id;
          // 找到对应挂号信息
          const appointInfo = appointResponse.data.data.find(item => item.app_if.AppointmentID === apId);
          if (!appointInfo) {
            console.error(`未找到挂号信息，apId: ${apId}`);
            return null;
          }
          const patientId = appointInfo.pat_if.PatientId;
          const userInfoResponse = await get_single_userinfo_data(patientId);
          const patient = userInfoResponse.data.data[0];
          const presResultResponse = await getPresResult(pres.pres_id);
          const drugsResponse = await getDrugs();
          const drug = drugsResponse.data.data.find(d => d.drug_name === pres.drug_name);

          let dosage = '暂无';
          let frequency = '暂无';
          let notes = '暂无';
          if (presResultResponse.data.data && presResultResponse.data.data.use_method) {
            const useMethodParts = presResultResponse.data.data.use_method.split('，');
            dosage = useMethodParts[0] || '暂无';
            frequency = useMethodParts[1] || '暂无';
          }
          if (presResultResponse.data.data && presResultResponse.data.data.doc_comment) {
            notes = presResultResponse.data.data.doc_comment;
          }

          return {
            id: pres.pres_id,
            ap_id: pres.ap_id,
            prescriptionNo: `RX${dayjs(pres.oper_time).format('YYYYMMDD')}${pres.pres_id.toString().padStart(3, '0')}`,
            patient: {
              id: patientId,
              name: patient.Username,
              gender: appointInfo.pat_if.PatientGender === 0 ? 'female' : 'male',
              age: appointInfo.pat_if.PatientAge
            },
            diagnosis: appointInfo.app_if.diagnosis || '暂无',
            date: pres.oper_time,
            status: getStatusFromCode(pres.status_code),
            amount: parseFloat(pres.sum_price),
            drugs: [
              {
                id: drug ? drug.drug_id : null,
                name: drug ? drug.drug_name : '暂无',
                specification: drug ? drug.drug_specification : '暂无',
                dosage,
                frequency,
                days: parseInt(pres.total_amount),
                quantity: parseInt(pres.total_amount),
                price: drug ? parseFloat(drug.drug_price) : 0
              }
            ],
            notes,
            doctor: {
              name: pres.doc_name,
              title: pres.doc_title
            },
            invalidateReason: null
          }
        }));
        prescriptions.value = processedPrescriptions.filter(p => p !== null);
        pagination.value.total = prescriptions.value.length;
      } else {
        message.error('获取处方信息失败')
      }
    } else {
      message.error('获取挂号信息失败')
    }
  } catch (error) {
    message.error('获取处方列表失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 根据状态码获取状态文本
const getStatusFromCode = (statusCode) => {
  const map = {
    0: 'valid',
    1: 'valid',
    2: 'dispensed'
  }
  return map[statusCode] || 'unknown'
}

// 格式化日期
const formatDate = date => {
  return date ? dayjs(date).format('YYYY-MM-DD') : '-'
}

// 获取状态文本
const getStatusText = status => {
  const map = {
    'valid': '有效',
    'expired': '已过期',
    'invalid': '已作废',
    'dispensed': '已取药'
  }
  return map[status] || status
}

// 获取状态颜色
const getStatusColor = status => {
  const map = {
    'valid': 'green',
    'expired': 'orange',
    'invalid': 'red',
    'dispensed': 'blue'
  }
  return map[status] || 'default'
}

// 处理查询
const handleSearch = async () => {
  try {
    const loginInfoResponse = await getLoginInfo()
    if (loginInfoResponse.data.code === 200) {
      const doctorId = loginInfoResponse.data.data.doctor_id
      pagination.value.current = 1
      await fetchPrescriptions(doctorId)
    } else {
      message.error('获取登录信息失败')
    }
  } catch (error) {
    message.error('查询失败: ' + error.message)
  }
}

// 重置查询
const resetSearch = async () => {
  filterForm.value = {
    status: undefined,
    patientName: '',
    dateRange: []
  }
  await handleSearch()
}

// 处理表格变化
const handleTableChange = async (pag, filters, sorter) => {
  try {
    const loginInfoResponse = await getLoginInfo()
    if (loginInfoResponse.data.code === 200) {
      const doctorId = loginInfoResponse.data.data.doctor_id
      pagination.value.current = pag.current
      pagination.value.pageSize = pag.pageSize
      await fetchPrescriptions(doctorId)
    } else {
      message.error('获取登录信息失败')
    }
  } catch (error) {
    message.error('表格数据更新失败: ' + error.message)
  }
}

// 查看处方详情
const viewPrescriptionDetail = async (record) => {
  currentPrescription.value = record
  try {
    const response = await getAppointinfo(record.ap_id);
    if (response.data.code === 200) {
      appointInfo.value = response.data.data;
    } else {
      // 若获取失败，将对应字段置空
      appointInfo.value = {
        pat_requirements: '',
        pat_history: '',
        diagnosis: '暂无'
      };
    }
  } catch (error) {
    // 若出现异常，将对应字段置空
    appointInfo.value = {
      pat_requirements: '',
      pat_history: '',
      diagnosis: '暂无'
    };
  }
  detailVisible.value = true;
}

// 显示作废模态框
const showInvalidateModal = record => {
  currentPrescription.value = record
  invalidateReason.value = ''
  invalidateVisible.value = true
}

// 处理作废处方
const handleInvalidatePrescription = async () => {
  if (!invalidateReason.value.trim()) {
    message.warning('请输入作废原因')
    return
  }
  
  try {
    loading.value = true
    // 这里需要添加实际的作废处方 API 调用
    // 假设存在一个 invalidatePrescription API
    // const response = await invalidatePrescription({ pres_id: currentPrescription.value.id, reason: invalidateReason.value })
    // if (response.data.code === 200) {
    // 更新本地数据
    const index = prescriptions.value.findIndex(p => p.id === currentPrescription.value.id)
    if (index !== -1) {
      prescriptions.value[index] = {
        ...prescriptions.value[index],
        status: 'invalid',
        invalidateReason: invalidateReason.value
      }
    }
    message.success('处方已作废')
    invalidateVisible.value = false
    // } else {
    //   message.error('作废处方失败')
    // }
  } catch (error) {
    message.error('作废处方失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 打印处方
const printPrescription = record => {
  message.loading({ content: '正在生成打印文件...', key: 'print' })
  // 模拟打印延迟
  setTimeout(() => {
    message.success({ content: `处方 ${record.prescriptionNo} 已发送至打印机`, key: 'print' })
  }, 1500)
}
</script>

<style scoped>
.prescription-management-container {
  padding: 24px;
}

.filter-container {
  margin-bottom: 24px;
}

.ant-descriptions-item {
  padding: 12px 24px;
}

.ant-descriptions-item-label {
  width: 120px;
  font-weight: 500;
}
</style>