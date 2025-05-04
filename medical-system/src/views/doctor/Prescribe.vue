<template>
  <div class="prescribe-container">
    <!-- 选择患者 -->
    <div class="select-patient">
      <a-select v-model:value="selectedAppointmentId" show-search placeholder="选择今天挂号的患者" style="width: 200px"
        @change="handlePatientChange">
        <a-select-option v-for="appointment in appointmentList" :key="appointment.app_if.AppointmentID"
          :value="appointment.app_if.AppointmentID">
          {{ appointment.pat_if.PatientName }} - {{ dayjs(appointment.app_if.AppointmentDateTime).format('HH:mm') }}
        </a-select-option>
      </a-select>
    </div>
    <a-card title="开具处方" :bordered="false">
      <!-- 患者信息 -->
      <div class="patient-info">
        <a-descriptions bordered :column="4" size="middle">
          <a-descriptions-item label="患者姓名">{{ selectedAppointment ? selectedAppointment.pat_if.PatientName : '暂无'
            }}</a-descriptions-item>
          <a-descriptions-item label="性别">{{ selectedAppointment ? (selectedAppointment.pat_if.PatientGender === 0 ? '女'
            : '男') : '暂无' }}</a-descriptions-item>
          <a-descriptions-item label="年龄">{{ selectedAppointment ? selectedAppointment.pat_if.PatientAge + '岁' : '暂无'
            }}</a-descriptions-item>
          <a-descriptions-item label="病历号">暂无</a-descriptions-item>
          <a-descriptions-item label="过往病史">
            <a-input v-model:value="patientInfo.pat_history" placeholder="请输入过往病史" style="width: 100%" />
          </a-descriptions-item>
          <a-descriptions-item label="主诉">
            <a-input v-model:value="patientInfo.pat_requirements" placeholder="请输入主诉" style="width: 100%" />
          </a-descriptions-item>
          <a-descriptions-item label="初步诊断" :span="2">
            <a-input v-model:value="diagnosis" placeholder="请输入诊断结果" style="width: 100%" />
          </a-descriptions-item>
        </a-descriptions>
      </div>

      <!-- 处方编辑区域 -->
      <div class="prescription-editor">
        <div class="drug-list">
          <h3>药品清单</h3>
          <a-table :columns="drugColumns" :data-source="prescriptionDrugs" :row-key="(record, index) => index"
            :pagination="false">
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'drug'">
                <a-select v-model:value="record.drugId" show-search placeholder="选择药品" style="width: 100%"
                  :filter-option="filterDrugOption" @change="handleDrugChange(index, $event)">
                  <a-select-option v-for="drug in drugList" :key="drug.drug_id" :value="drug.drug_id">
                    {{ drug.drug_name }}
                  </a-select-option>
                </a-select>
              </template>
              <template v-else-if="column.key === 'usage'">
                <a-row :gutter="8">
                  <a-col :span="12">
                    <a-select v-model:value="record.dosage" placeholder="用量" style="width: 100%">
                      <a-select-option v-for="d in dosages" :key="d" :value="d">
                        {{ d }}
                      </a-select-option>
                    </a-select>
                  </a-col>
                  <a-col :span="12">
                    <a-select v-model:value="record.frequency" placeholder="频次" style="width: 100%">
                      <a-select-option v-for="f in frequencies" :key="f" :value="f">
                        {{ f }}
                      </a-select-option>
                    </a-select>
                  </a-col>
                </a-row>
              </template>
              <template v-else-if="column.key === 'days'">
                <a-input-number v-model:value="record.days" :min="1" :max="30" placeholder="天数" style="width: 100%" />
              </template>
              <template v-else-if="column.key === 'quantity'">
                <a-input-number v-model:value="record.quantity" :min="1" :max="10" placeholder="数量"
                  style="width: 100%" />
              </template>
              <template v-else-if="column.key === 'action'">
                <a-button type="link" danger @click="removeDrug(index)">
                  <DeleteOutlined />
                </a-button>
              </template>
            </template>
          </a-table>
          <div class="add-drug">
            <a-button type="dashed" @click="addDrug">
              <PlusOutlined />
              添加药品
            </a-button>
          </div>
        </div>

        <!-- 医嘱备注 -->
        <div class="prescription-notes">
          <h3>医嘱备注</h3>
          <a-textarea v-model:value="notes" placeholder="请输入用药注意事项或其他医嘱" :rows="3" :maxlength="200" show-count />
        </div>
      </div>

      <!-- 处方预览 -->
      <div class="prescription-preview">
        <h3>处方预览</h3>
        <div class="preview-content">
          <div class="prescription-header">
            <div class="hospital-name">XX医院电子处方笺</div>
            <div class="prescription-no">处方编号: {{ prescriptionNo }}</div>
          </div>
          <div class="prescription-body">
            <div class="patient-info">
              <span>姓名: {{ selectedAppointment ? selectedAppointment.pat_if.PatientName : '暂无' }}</span>
              <span>性别: {{ selectedAppointment ? (selectedAppointment.pat_if.PatientGender === 0 ? '女' : '男') : '暂无'
                }}</span>
              <span>年龄: {{ selectedAppointment ? selectedAppointment.pat_if.PatientAge + '岁' : '暂无' }}</span>
              <span>病历号: 暂无</span>
            </div>
            <div class="diagnosis">
              <strong>诊断:</strong> {{ diagnosis || '暂无' }}
            </div>
            <div class="drugs-list">
              <a-table :columns="previewColumns" :data-source="prescriptionDrugs" :row-key="(record, index) => index"
                :pagination="false" bordered>
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'drugInfo'">
                    <div>{{ record.name }}</div>
                    <div class="spec">{{ record.specification }}</div>
                  </template>
                  <template v-else-if="column.key === 'usage'">
                    {{ record.dosage }}，{{ record.frequency }}，{{ record.days }}天
                  </template>
                  <template v-else-if="column.key === 'total'">
                    {{ record.quantity * (record.price || 0) }}元
                  </template>
                </template>
              </a-table>
            </div>
            <div class="total-amount">
              合计金额: <span class="amount">{{ totalAmount.toFixed(2) }}</span> 元
            </div>
            <div class="prescription-notes">
              <strong>医嘱:</strong> {{ notes || '无' }}
            </div>
          </div>
          <div class="prescription-footer">
            <div class="doctor-sign">
              <div>医师: {{ userInfo.name }}</div>
              <div>开具日期: {{ currentDate }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 操作按钮 -->
      <div class="actions">
        <a-button @click="resetForm">重置</a-button>
        <a-button type="primary" @click="saveDraft" :loading="saving">保存草稿</a-button>
        <a-button type="primary" @click="submitPrescription" :loading="submitting">提交处方</a-button>
        <a-button type="primary" @click="savePatientInfo" :loading="savingPatientInfo">保存患者信息</a-button>
      </div>
    </a-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons-vue'
import { getDrugs, addPrescriptions, addPresResult, updatePresResult, getAppoint, getLoginInfo, getSchedules, getAppointinfo, addAppointinfo, updateAppointinfo } from '@/api/api.js' // 假设 API 函数都在 api.js 文件中

// 当前用户信息
const userInfo = ref({
  name: '未知医生',
  title: '',
  department: ''
})
const doctorId = ref(null)

// 当前选择的预约信息
const selectedAppointment = ref(null)
const selectedAppointmentId = ref(null)
const appointmentList = ref([])

// 处方表单数据
const diagnosis = ref('')
const notes = ref('')
const prescriptionNo = ref(`RX${dayjs().format('YYYYMMDD')}${Math.floor(Math.random() * 1000)}`)
const currentDate = ref(dayjs().format('YYYY年MM月DD日'))

// 药品数据
const drugList = ref([])

// 用法用量选项
const dosages = ref(['1片', '2片', '1袋', '5ml', '10ml', '1支'])
const frequencies = ref(['每日一次', '每日两次', '每日三次', '每四小时一次', '必要时'])

// 处方药品列表
const prescriptionDrugs = ref([
  { drugId: '', name: '', specification: '', dosage: '', frequency: '', days: 3, quantity: 1, price: 0 }
])

// 表格列定义
const drugColumns = [
  {
    title: '药品名称',
    key: 'drug',
    width: '30%'
  },
  {
    title: '用法用量',
    key: 'usage',
    width: '30%'
  },
  {
    title: '天数',
    key: 'days',
    width: '15%'
  },
  {
    title: '数量',
    key: 'quantity',
    width: '15%'
  },
  {
    title: '操作',
    key: 'action',
    width: '10%'
  }
]

const previewColumns = [
  {
    title: '药品信息',
    dataIndex: 'drugInfo',
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

// 计算属性
const totalAmount = computed(() => {
  return prescriptionDrugs.value.reduce((sum, drug) => {
    return sum + (drug.quantity || 0) * (drug.price || 0)
  }, 0)
})

// 状态
const saving = ref(false)
const submitting = ref(false)
const savingPatientInfo = ref(false)

// 患者信息
const patientInfo = ref({
  ap_id: null,
  pat_requirements: '',
  pat_history: '',
  pat_now_history: '',
  diagnosis: '',
  treat_method: ''
})

// 方法
const filterDrugOption = (input, option) => {
  return option.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
}

const handleDrugChange = (index, drugId) => {
  const selectedDrug = drugList.value.find(d => d.drug_id === drugId)
  if (selectedDrug) {
    prescriptionDrugs.value[index] = {
      ...prescriptionDrugs.value[index],
      name: selectedDrug.drug_name,
      specification: '', // 原数据中无规格信息
      price: parseFloat(selectedDrug.drug_price)
    }
  }
}

const addDrug = () => {
  prescriptionDrugs.value.push({
    drugId: '',
    name: '',
    specification: '',
    dosage: '',
    frequency: '',
    days: 3,
    quantity: 1,
    price: 0
  })
}

const removeDrug = (index) => {
  if (prescriptionDrugs.value.length > 1) {
    prescriptionDrugs.value.splice(index, 1)
  } else {
    message.warning('至少需要一种药品')
  }
}

const resetForm = () => {
  diagnosis.value = ''
  notes.value = ''
  prescriptionDrugs.value = [{
    drugId: '',
    name: '',
    specification: '',
    dosage: '',
    frequency: '',
    days: 3,
    quantity: 1,
    price: 0
  }]
  message.info('已重置处方表单')
}

const saveDraft = async () => {
  if (!validatePrescription()) return

  try {
    saving.value = true
    // 保存草稿逻辑可以根据实际需求实现
    message.success('处方草稿保存成功')
  } catch (error) {
    message.error('保存草稿失败')
  } finally {
    saving.value = false
  }
}

const submitPrescription = async () => {
  if (!validatePrescription()) return

  try {
    submitting.value = true
    // 依次调用 API 进行开药、付款、发药操作
    for (const drug of prescriptionDrugs.value) {
      const formData = new FormData()
      formData.append('appointment_id', selectedAppointment.value ? selectedAppointment.value.app_if.AppointmentID : null)
      formData.append('drug_id', drug.drugId)
      formData.append('amount', drug.quantity)
      formData.append('oper_code', 0)

      // 开药
      const response = await addPrescriptions(formData)
      console.log(response.data)
      const presId = response.data.data.pres_id
      // console.log(presId.value);

      // 添加医嘱和用量
      const presResultFormData = new FormData();
      presResultFormData.append('pres_id', response.data.data.pres_id)
      presResultFormData.append('use_method', `${drug.dosage}，${drug.frequency}`)
      presResultFormData.append('doc_comment', notes.value)
      //console.log(presResultFormData.get('pres_id'), presResultFormData.get('use_method'))
      const addPresResultResponse = await addPresResult(presResultFormData)
      console.log(addPresResultResponse.data)

      // 付款和发药操作可以根据实际需求添加
    }

    // 生成新的处方编号
    prescriptionNo.value = `RX${dayjs().format('YYYYMMDD')}${Math.floor(Math.random() * 1000)}`
    currentDate.value = dayjs().format('YYYY年MM月DD日')

    message.success('处方提交成功')
    resetForm()
  } catch (error) {
    message.error('处方提交失败' + error.message)
  } finally {
    submitting.value = false
  }
}

const validatePrescription = () => {
  if (!diagnosis.value) {
    message.warning('请输入诊断结果')
    return false
  }

  const invalidDrugs = prescriptionDrugs.value.filter(d =>
    !d.drugId || !d.dosage || !d.frequency || !d.days || !d.quantity
  )

  if (invalidDrugs.length > 0) {
    message.warning('请完善所有药品信息')
    return false
  }

  return true
}

const handlePatientChange = async () => {
  const appointment = appointmentList.value.find(app => app.app_if.AppointmentID === selectedAppointmentId.value);
  if (appointment) {
    selectedAppointment.value = appointment;
    console.log('Selected appointment:', selectedAppointment.value);

    try {
      const response = await getAppointinfo(appointment.app_if.AppointmentID);
      if (response.data.code === 200) {
        patientInfo.value = {
          ap_id: response.data.data.ap_id,
          pat_requirements: response.data.data.pat_requirements || '',
          pat_history: response.data.data.pat_history || '',
          pat_now_history: response.data.data.pat_now_history || '',
          diagnosis: response.data.data.diagnosis || '',
          treat_method: response.data.data.treat_method || ''
        };
      }
    } catch (error) {
      message.error('获取患者信息失败');
    }
  }
};

const savePatientInfo = async () => {
  try {
    savingPatientInfo.value = true;
    const formData = new FormData();
    formData.append('ap_id', patientInfo.value.ap_id);
    formData.append('pat_requirements', patientInfo.value.pat_requirements);
    formData.append('pat_history', patientInfo.value.pat_history);
    formData.append('pat_now_history', patientInfo.value.pat_now_history);
    formData.append('diagnosis', diagnosis.value);
    formData.append('treat_method', patientInfo.value.treat_method);

    let response;
    if (patientInfo.value.ap_id) {
      response = await updateAppointinfo(formData);
    } else {
      response = await addAppointinfo(formData);
    }

    if (response.data.code === 200) {
      message.success('患者信息保存成功');
    } else {
      message.error('患者信息保存失败');
    }
  } catch (error) {
    message.error('患者信息保存失败');
  } finally {
    savingPatientInfo.value = false;
  }
};

// 初始化
onMounted(async () => {
  try {
    // 获取当前用户登录信息
    const loginInfoResponse = await getLoginInfo();
    doctorId.value = loginInfoResponse.data.data.doctor_id;
    userInfo.value.name = '医生' + doctorId.value;

    // 获取今天的日期
    const today = dayjs().format('YYYY-MM-DD');

    // 获取今天的排班信息
    const schedulesResponse = await getSchedules({ start_date: today, end_date: today });
    const scheduleIds = schedulesResponse.data.data.filter(s => s.doctor_id === doctorId.value).map(s => s.schedule_id);

    // 获取挂号信息
    const appointResponse = await getAppoint({ doctor_id: doctorId.value });
    // 筛选出今天的挂号信息
    appointmentList.value = appointResponse.data.data.filter(appointment => {
      return dayjs(appointment.app_if.AppointmentDateTime).format('YYYY-MM-DD') === today;
    });
    console.log('appointmentList:', appointmentList.value); // 输出患者列表数据

    // 获取药品列表
    const drugsResponse = await getDrugs();
    drugList.value = drugsResponse.data.data;
  } catch (error) {
    message.error('数据加载失败');
    console.error('数据加载错误:', error); // 输出错误信息
  }
});
</script>

<style scoped>
.prescribe-container {
  padding: 24px;
}

.select-patient {
  margin-bottom: 24px;
}

.patient-info {
  margin-bottom: 24px;
}

.prescription-editor {
  margin-bottom: 24px;
}

.drug-list h3,
.prescription-notes h3,
.prescription-preview h3 {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 16px;
  color: rgba(0, 0, 0, 0.85);
}

.add-drug {
  margin-top: 16px;
  text-align: center;
}

.prescription-notes {
  margin-top: 24px;
}

.prescription-preview {
  margin-top: 32px;
  padding: 24px;
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
}

.preview-content {
  font-family: 'Microsoft YaHei', sans-serif;
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

.patient-info {
  display: flex;
  gap: 24px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.diagnosis {
  margin-bottom: 16px;
}

.drugs-list {
  margin: 16px 0;
}

.total-amount {
  text-align: right;
  margin: 16px 0;
  font-size: 16px;
}

.amount {
  font-weight: bold;
  color: #f5222d;
}

.prescription-notes {
  margin: 16px 0;
}

.prescription-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 40px;
}

.doctor-sign {
  text-align: right;
}

.spec {
  font-size: 12px;
  color: #666;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  margin-top: 24px;
}
</style>