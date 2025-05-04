<template>
  <div class="checkup-container">
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
    <a-card title="开具检查" :bordered="false">
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

      <!-- 检查项目编辑区域 -->
      <div class="checkup-editor">
        <div class="checkup-list">
          <h3>检查项目清单</h3>
          <a-table
            :columns="checkupColumns"
            :data-source="checkupItems"
            :row-key="(record, index) => index"
            :pagination="false"
          >
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'checkup'">
                <a-select
                  v-model:value="record.checkupId"
                  show-search
                  placeholder="选择检查项目"
                  style="width: 100%"
                  :filter-option="filterCheckupOption"
                  @change="handleCheckupChange(index, $event)"
                >
                  <a-select-option 
                    v-for="checkup in checkupList" 
                    :key="checkup.id"
                    :value="checkup.id"
                  >
                    {{ checkup.name }}
                  </a-select-option>
                </a-select>
              </template>
              <template v-else-if="column.key === 'action'">
                <a-button type="link" danger @click="removeCheckup(index)">
                  <DeleteOutlined />
                </a-button>
              </template>
            </template>
          </a-table>
          <div class="add-checkup">
            <a-button type="dashed" @click="addCheckup">
              <PlusOutlined />
              添加检查项目
            </a-button>
          </div>
        </div>

        <!-- 检查备注 -->
        <div class="checkup-notes">
          <h3>检查备注</h3>
          <a-textarea
            v-model:value="notes"
            placeholder="请输入检查注意事项或其他备注"
            :rows="3"
            :maxlength="200"
            show-count
          />
        </div>
      </div>

      <!-- 检查单预览 -->
      <div class="checkup-preview">
        <h3>检查单预览</h3>
        <div class="preview-content">
          <div class="checkup-header">
            <div class="hospital-name">XX医院检查申请单</div>
            <div class="checkup-no">检查编号: {{ checkupNo }}</div>
          </div>
          <div class="checkup-body">
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
            <div class="checkup-items-list">
              <a-table
                :columns="previewColumns"
                :data-source="checkupItems"
                :row-key="(record, index) => index"
                :pagination="false"
                bordered
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'checkupInfo'">
                    <div>{{ record.name }}</div>
                  </template>
                  <template v-else-if="column.key === 'price'">
                    {{ record.price }}元
                  </template>
                </template>
              </a-table>
            </div>
            <div class="total-amount">
              合计金额: <span class="amount">{{ totalAmount.toFixed(2) }}</span> 元
            </div>
            <div class="checkup-notes">
              <strong>备注:</strong> {{ notes || '无' }}
            </div>
          </div>
          <div class="checkup-footer">
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
        <a-button type="primary" @click="submitCheckup" :loading="submitting">提交检查单</a-button>
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
import { getAppoint, getLoginInfo, getCheckup, addCheckupRecord, getAppointinfo, addAppointinfo, updateAppointinfo } from '@/api/api.js'

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

// 检查单表单数据
const diagnosis = ref('')
const notes = ref('')
const checkupNo = ref(`CX${dayjs().format('YYYYMMDD')}${Math.floor(Math.random() * 1000)}`)
const currentDate = ref(dayjs().format('YYYY年MM月DD日'))

// 检查项目数据
const checkupList = ref([])

// 检查项目列表
const checkupItems = ref([
  { checkupId: '', name: '', price: 0 }
])

// 表格列定义
const checkupColumns = [
  {
    title: '检查项目名称',
    key: 'checkup',
    width: '90%'
  },
  {
    title: '操作',
    key: 'action',
    width: '10%'
  }
]

const previewColumns = [
  {
    title: '检查项目信息',
    dataIndex: 'checkupInfo',
    key: 'checkupInfo',
    width: '80%'
  },
  {
    title: '单价(元)',
    dataIndex: 'price',
    key: 'price',
    align: 'right',
    width: '20%'
  }
]

// 计算属性
const totalAmount = computed(() => {
  return checkupItems.value.reduce((sum, item) => {
    return sum + (item.price || 0)
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
const filterCheckupOption = (input, option) => {
  return option.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
}

const handleCheckupChange = (index, checkupId) => {
  const selectedCheckup = checkupList.value.find(c => c.id === checkupId)
  if (selectedCheckup) {
    checkupItems.value[index] = {
      ...checkupItems.value[index],
      name: selectedCheckup.name,
      price: parseFloat(selectedCheckup.price)
    }
  }
}

const addCheckup = () => {
  checkupItems.value.push({
    checkupId: '',
    name: '',
    price: 0
  })
}

const removeCheckup = (index) => {
  if (checkupItems.value.length > 1) {
    checkupItems.value.splice(index, 1)
  } else {
    message.warning('至少需要一项检查项目')
  }
}

const resetForm = () => {
  diagnosis.value = ''
  notes.value = ''
  checkupItems.value = [{
    checkupId: '', 
    name: '', 
    price: 0
  }]
  message.info('已重置检查单表单')
}

const saveDraft = async () => {
  if (!validateCheckup()) return
  
  try {
    saving.value = true
    // 这里可以添加保存草稿的逻辑，例如将数据存储到本地缓存
    message.success('检查单草稿保存成功')
  } catch (error) {
    message.error('保存草稿失败')
  } finally {
    saving.value = false
  }
}

const submitCheckup = async () => {
  if (!validateCheckup()) return
  
  try {
    submitting.value = true
    if (selectedAppointmentId.value) {
      const currentAppointment = appointmentList.value.find(p => p.app_if.AppointmentID === selectedAppointmentId.value)
      const apId = currentAppointment.app_if.AppointmentID
      for (const item of checkupItems.value) {
        const formData = new FormData()
        formData.append('ap_id', apId)
        formData.append('exam_def_id', item.checkupId)
        const response = await addCheckupRecord(formData)
        console.log(response.data)
        if (response.data.code !== 200) {
          throw new Error('提交检查记录失败')
        }
      }
    }
    
    // 生成新的检查编号
    checkupNo.value = `CX${dayjs().format('YYYYMMDD')}${Math.floor(Math.random() * 1000)}`
    currentDate.value = dayjs().format('YYYY年MM月DD日')
    
    message.success('检查单提交成功')
    resetForm()
  } catch (error) {
    message.error('检查单提交失败' + error.message)
  } finally {
    submitting.value = false
  }
}

const validateCheckup = () => {
  if (!diagnosis.value) {
    message.warning('请输入诊断结果')
    return false
  }
  
  const invalidItems = checkupItems.value.filter(c => 
    !c.checkupId
  )
  
  if (invalidItems.length > 0) {
    message.warning('请完善所有检查项目信息')
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
    formData.append('diagnosis', patientInfo.value.diagnosis);
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

    // 获取今天的挂号信息
    const appointResponse = await getAppoint({ doctor_id: doctorId.value });
    // 筛选出今天的挂号信息
    appointmentList.value = appointResponse.data.data.filter(appointment => {
      return dayjs(appointment.app_if.AppointmentDateTime).format('YYYY-MM-DD') === today;
    });
    console.log('appointmentList:', appointmentList.value); // 输出患者列表数据

    // 获取检查项目
    const checkupResponse = await getCheckup({});
    if (checkupResponse.data.code === 200) {
      checkupList.value = checkupResponse.data.data.map(item => ({
        id: item.exam_def_id,
        name: item.exam_name,
        price: item.exam_price
      }));
    } else {
      message.error('获取检查项目失败');
    }
  } catch (error) {
    message.error('数据加载失败');
    console.error('数据加载错误:', error); // 输出错误信息
  }
});
</script>

<style scoped>
.checkup-container {
  padding: 24px;
}

.select-patient {
  margin-bottom: 24px;
}

.patient-info {
  margin-bottom: 24px;
}

.checkup-editor {
  margin-bottom: 24px;
}

.checkup-list h3, .checkup-notes h3, .checkup-preview h3 {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 16px;
  color: rgba(0, 0, 0, 0.85);
}

.add-checkup {
  margin-top: 16px;
  text-align: center;
}

.checkup-notes {
  margin-top: 24px;
}

.checkup-preview {
  margin-top: 32px;
  padding: 24px;
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
}

.preview-content {
  font-family: 'Microsoft YaHei', sans-serif;
}

.checkup-header {
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

.checkup-no {
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

.checkup-items-list {
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

.checkup-notes {
  margin: 16px 0;
}

.checkup-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 40px;
}

.doctor-sign {
  text-align: right;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  margin-top: 24px;
}
</style>
    