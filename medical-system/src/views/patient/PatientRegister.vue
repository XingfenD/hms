<template>
  <div class="register-container">
    <a-card title="预约挂号" :bordered="false">
      <a-steps :current="currentStep" style="margin-bottom: 32px;">
        <a-step title="选择科室" />
        <a-step title="选择医生" />
        <a-step title="选择时间" />
        <a-step title="确认信息" />
      </a-steps>

      <!-- 步骤1: 选择科室 -->
      <div v-if="currentStep === 0" class="step-content">
        <a-form layout="vertical">
          <a-form-item label="就诊日期">
            <a-date-picker 
              v-model:value="formData.date" 
              style="width: 100%" 
              :disabled-date="disabledDate"
            />
          </a-form-item>
          <a-form-item label="班次">
            <a-radio-group v-model:value="formData.shift">
              <a-radio-button value="morning">上午 (8:30-12:00)</a-radio-button>
              <a-radio-button value="afternoon">下午 (14:00-17:30)</a-radio-button>
              <a-radio-button value="night">晚班 (18:00-7:00)</a-radio-button>
            </a-radio-group>
          </a-form-item>
          <a-form-item label="科室">
            <a-select
              v-model:value="formData.department"
              placeholder="请选择科室"
              style="width: 100%"
              @change="handleDepartmentChange"
            >
              <a-select-option 
                v-for="dept in departments" 
                :key="dept.id"
                :value="dept.id"
              >
                {{ dept.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-form>
      </div>

      <!-- 步骤2: 选择医生 -->
      <div v-if="currentStep === 1" class="step-content">
        <a-form layout="vertical">
          <a-form-item label="选择医生">
            <a-select
              v-model:value="formData.doctor"
              placeholder="请选择医生"
              style="width: 100%"
            >
              <a-select-option 
                v-for="doctor in doctors" 
                :key="doctor.own_id"
                :value="doctor.own_id"
              >
                {{ doctor.name }} ({{ doctor.title }}) - 剩余号源: {{ doctor.available }}
              </a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="医生简介">
            <div class="doctor-info" v-if="selectedDoctor">
              <a-avatar :src="selectedDoctor.avatar" :size="64" />
              <div class="doctor-details">
                <h3>{{ selectedDoctor.name }} {{ selectedDoctor.title }}</h3>
                <p>{{ selectedDoctor.specialty }}</p>
                <p>{{ selectedDoctor.description }}</p>
              </div>
            </div>
            <a-empty v-else description="请先选择医生" />
          </a-form-item>
        </a-form>
      </div>

      <!-- 步骤3: 选择时间 -->
      <div v-if="currentStep === 2" class="step-content">
        <a-form layout="vertical">
          <a-form-item label="选择时间段">
            <a-radio-group v-model:value="formData.timeSlot" style="width: 100%">
              <a-row :gutter="[16, 16]">
                <a-col :span="8" v-for="slot in timeSlots" :key="slot">
                  <a-radio-button :value="slot" :disabled="isSlotDisabled(slot)">
                    {{ slot }}
                  </a-radio-button>
                </a-col>
              </a-row>
            </a-radio-group>
          </a-form-item>
        </a-form>
      </div>

      <!-- 步骤4: 确认信息 -->
      <div v-if="currentStep === 3" class="step-content">
        <a-descriptions bordered :column="1">
          <a-descriptions-item label="就诊日期">{{ formatDate(formData.date) }}</a-descriptions-item>
          <a-descriptions-item label="班次">{{ getShiftText(formData.shift) }}</a-descriptions-item>
          <a-descriptions-item label="科室">{{ selectedDepartment?.name }}</a-descriptions-item>
          <a-descriptions-item label="医生">{{ selectedDoctor?.name }} {{ selectedDoctor?.title }}</a-descriptions-item>
          <a-descriptions-item label="就诊时间">{{ formData.timeSlot }}</a-descriptions-item>
        </a-descriptions>
        
        <a-form-item label="病情描述" style="margin-top: 24px;">
          <a-textarea 
            v-model:value="formData.description" 
            placeholder="请简要描述您的病情或症状" 
            :rows="4"
          />
        </a-form-item>
        <a-form-item label="主诉">
          <a-textarea 
            v-model:value="formData.pat_requirements" 
            placeholder="请填写主诉" 
            :rows="4"
          />
        </a-form-item>
        <a-form-item label="过往病史">
          <a-textarea 
            v-model:value="formData.pat_history" 
            placeholder="请填写过往病史" 
            :rows="4"
          />
        </a-form-item>
        <a-form-item label="现病史">
          <a-textarea 
            v-model:value="formData.pat_now_history" 
            placeholder="请填写现病史" 
            :rows="4"
          />
        </a-form-item>
      </div>

      <div class="actions">
        <a-button v-if="currentStep > 0" @click="prevStep">上一步</a-button>
        <a-button 
          v-if="currentStep < 3" 
          type="primary" 
          @click="nextStep"
          :disabled="!canProceed"
        >
          下一步
        </a-button>
        <a-button 
          v-if="currentStep === 3" 
          type="primary" 
          @click="submitRegistration"
          :loading="submitting"
        >
          提交预约
        </a-button>
      </div>
    </a-card>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'
import { addAppoint, getSchedules, getDoctorsByDepartment, getLoginInfo, addAppointinfo } from '@/api/api'

// 步骤控制
const currentStep = ref(0)
const submitting = ref(false)

// 表单数据
const formData = ref({
  date: null,
  shift: 'morning',
  department: null,
  doctor: null,
  timeSlot: null,
  description: '',
  pat_requirements: '',
  pat_history: '',
  pat_now_history: ''
})

// 模拟数据
const departments = ref([
  { id: 1, name: '内科' },
  { id: 2, name: '外科' },
  { id: 3, name: '儿科' },
  { id: 4, name: '妇产科' },
  { id: 5, name: '眼科' }
])

const doctors = ref([])
const timeSlots = ref([])
const schedules = ref([])

// 计算属性
const selectedDepartment = computed(() => {
  return departments.value.find(d => d.id === formData.value.department)
})

const selectedDoctor = computed(() => {
  return doctors.value.find(d => d.id === formData.value.doctor)
})

const canProceed = computed(() => {
  switch(currentStep.value) {
    case 0: return formData.value.date && formData.value.department
    case 1: return formData.value.doctor
    case 2: return formData.value.timeSlot
    default: return true
  }
})

// 方法
const disabledDate = current => {
  // 不能选择今天之前的日期
  return current && current < dayjs().startOf('day')
}

const handleDepartmentChange = async (deptId) => {
  // 根据科室加载医生数据
  formData.value.doctor = null
  try {
    const response = await getDoctorsByDepartment(deptId)
    console.log(response.data);
    doctors.value = response.data.data.map(doctor => ({
      id: doctor.DoctorID,
      name: doctor.FullName,
      title: '', // 这里假设没有 title 数据，可根据实际情况调整
      specialty: '', // 这里假设没有 specialty 数据，可根据实际情况调整
      available: 5, // 这里假设剩余号源为 5，可根据实际情况调整
      description: '', // 这里假设没有 description 数据，可根据实际情况调整
      avatar: '',
      own_id: doctor.DoctorOwnID
    }))
    if (doctors.value.length === 0) {
      message.warning('该科室暂无可用医生，请重新选择科室')
    }
  } catch (error) {
    message.error('获取医生数据失败，请稍后重试')
  }
}

const isSlotDisabled = slot => {
  // 模拟某些时间段不可选
  return slot === '10:00-10:30' || slot === '14:30-15:00'
}

const nextStep = async () => {
  if (currentStep.value === 0) {
    if (!formData.value.date || !formData.value.department) {
      message.warning('请选择就诊日期和科室')
      return
    }
    if (doctors.value.length === 0) {
      message.warning('该科室暂无可用医生，请重新选择科室')
      return
    }
  }
  if (currentStep.value === 1) {
    if (!formData.value.doctor) {
      message.warning('请选择医生')
      return
    }
    const appointmentDate = formatDate(formData.value.date)
    const scheduleResponse = await getSchedules({
      start_date: appointmentDate,
      end_date: appointmentDate
    })
    const doctorSchedules = scheduleResponse.data.data.filter(schedule => schedule.doctor_id === formData.value.doctor)
    const schedule = doctorSchedules[0]
    if (!schedule) {
      message.warning('该医生在所选日期暂无排班，请重新选择')
      return
    }
    const { startTime, endTime } = getShiftTimeRange(formData.value.shift)
    const slots = generateTimeSlots(startTime, endTime)
    timeSlots.value = slots
    if (timeSlots.value.length === 0) {
      message.warning('该医生在所选日期暂无可用时间段，请重新选择')
      return
    }
  }
  currentStep.value++
}

const prevStep = () => {
  currentStep.value--
}

const formatDate = date => {
  return dayjs(date).format('YYYY-MM-DD')
}

const getShiftText = (shift) => {
  switch (shift) {
    case 'morning':
      return '上午'
    case 'afternoon':
      return '下午'
    case 'night':
      return '晚班'
    default:
      return ''
  }
}

const getShiftTimeRange = (shift) => {
  switch (shift) {
    case 'morning':
      return { startTime: dayjs('08:30', 'HH:mm'), endTime: dayjs('12:00', 'HH:mm') }
    case 'afternoon':
      return { startTime: dayjs('14:00', 'HH:mm'), endTime: dayjs('17:30', 'HH:mm') }
    case 'night':
      return { startTime: dayjs('18:00', 'HH:mm'), endTime: dayjs('07:00', 'HH:mm').add(1, 'day') }
    default:
      return { startTime: dayjs('00:00', 'HH:mm'), endTime: dayjs('00:00', 'HH:mm') }
  }
}

const submitRegistration = async () => {
  submitting.value = true
  try {
    const appointmentDate = formatDate(formData.value.date)
    const appointmentTime = formData.value.timeSlot.split('-')[0]

    // 获取当前患者 ID
    const loginInfoResponse = await getLoginInfo()
    const patientId = loginInfoResponse.data.data.user_id

    // 获取排班 id
    const scheduleResponse = await getSchedules({
      start_date: appointmentDate,
      end_date: appointmentDate
    })
    const doctorSchedules = scheduleResponse.data.data.filter(schedule => schedule.doctor_id === formData.value.doctor)
    const schedule = doctorSchedules[0]
    console.log(scheduleResponse.data)
    const scheduleId = schedule?.schedule_id

    if (!scheduleId) {
      message.error('未找到合适的排班信息，请重新选择')
      return
    }

    const formDataToSend = new FormData()
    formDataToSend.append('patient_id', patientId)
    formDataToSend.append('schedule_id', scheduleId)
    formDataToSend.append('appointment_date', appointmentDate)
    formDataToSend.append('appointment_time', appointmentTime)

    const response = await addAppoint(formDataToSend)
    console.log(response.data);
    message.success('预约成功！')

    // 添加主诉、过往病史、现病史
    const apId = response.data.data.appointment_id 
    const appointInfoFormData = new FormData()
    appointInfoFormData.append('ap_id', apId)
    appointInfoFormData.append('pat_requirements', formData.value.pat_requirements)
    appointInfoFormData.append('pat_history', formData.value.pat_history)
    appointInfoFormData.append('pat_now_history', formData.value.pat_now_history)

    const appointInfoResponse = await addAppointinfo(appointInfoFormData)
    console.log(appointInfoResponse.data)

    // 这里可以跳转到预约管理页面或显示预约成功信息
  } catch (error) {
    message.error('预约失败，请稍后重试')
  } finally {
    submitting.value = false
  }
}

// 监听科室变化
watch(() => formData.value.department, handleDepartmentChange)

// 监听步骤变化
watch([currentStep, formData], async ([newStep, newFormData]) => {
  if (newStep === 2) {
    const appointmentDate = formatDate(newFormData.date)
    const scheduleResponse = await getSchedules({
      start_date: appointmentDate,
      end_date: appointmentDate
    })
    const doctorSchedules = scheduleResponse.data.data.filter(schedule => schedule.doctor_id === newFormData.doctor)
    const schedule = doctorSchedules[0]
    if (schedule) {
      const { startTime, endTime } = getShiftTimeRange(newFormData.shift)
      const slots = generateTimeSlots(startTime, endTime)
      timeSlots.value = slots
    }
  }
})

const generateTimeSlots = (startTime, endTime) => {
  const slots = []
  if (startTime.isAfter(endTime)) {
    // 晚班情况
    let currentTime = startTime
    const endOfDay = dayjs('23:59', 'HH:mm')
    while (currentTime.isBefore(endOfDay)) {
      const nextTime = currentTime.add(30, 'minute')
      slots.push(`${currentTime.format('HH:mm')}-${nextTime.format('HH:mm')}`)
      currentTime = nextTime
    }
    const startOfNextDay = dayjs('00:00', 'HH:mm').add(1, 'day')
    currentTime = startOfNextDay
    while (currentTime.isBefore(endTime)) {
      const nextTime = currentTime.add(30, 'minute')
      slots.push(`${currentTime.format('HH:mm')}-${nextTime.format('HH:mm')}`)
      currentTime = nextTime
    }
  } else {
    // 早班和中班情况
    let currentTime = startTime
    while (currentTime.isBefore(endTime)) {
      const nextTime = currentTime.add(30, 'minute')
      slots.push(`${currentTime.format('HH:mm')}-${nextTime.format('HH:mm')}`)
      currentTime = nextTime
    }
  }
  return slots
}
</script>

<style scoped>
.register-container {
  padding: 24px;
}

.step-content {
  min-height: 300px;
  padding: 24px 0;
}

.doctor-info {
  display: flex;
  gap: 16px;
  padding: 12px;
  background: #f9f9f9;
  border-radius: 4px;
}

.doctor-details {
  flex: 1;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  margin-top: 24px;
}
</style>    