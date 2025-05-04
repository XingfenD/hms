<template>
  <div class="appointment-container">
    <a-card title="我的预约" :bordered="false">
      <div class="filter-container">
        <a-form layout="inline" :model="filterForm">
          <a-form-item label="预约状态">
            <a-select
              v-model:value="filterForm.status"
              style="width: 150px"
              placeholder="全部状态"
              allow-clear
            >
              <a-select-option value="pending">已预约</a-select-option>
              <a-select-option value="in_progress">正在进行</a-select-option>
              <a-select-option value="completed">已结束</a-select-option>
              <a-select-option value="missed">过号</a-select-option>
              <a-select-option value="checked_in">患者已签到</a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="预约日期">
            <a-range-picker
              v-model:value="filterForm.dateRange"
              style="width: 240px"
              :disabled-date="disabledDate"
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

      <a-table
        :columns="columns"
        :data-source="appointments"
        :row-key="record => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ getStatusText(record.status) }}
            </a-tag>
          </template>
          <template v-else-if="column.key === 'action'">
            <a-space>
              <a-button
                size="small"
                @click="showDetail(record)"
              >
                详情
              </a-button>
              <a-button
                size="small"
                type="primary"
                :disabled="record.status === 'completed' || record.status === 'cancelled'"
                @click="showCancelModal(record)"
              >
                取消
              </a-button>
              <a-button
                size="small"
                :disabled="!(record.status === 'pending' && isSameDay(record.appointmentDate))"
                @click="handleCheckIn(record)"
              >
                签到
              </a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>

    <!-- 预约详情模态框 -->
    <a-modal
      v-model:visible="detailVisible"
      title="预约详情"
      width="700px"
      :footer="null"
    >
      <a-descriptions bordered :column="1">
        <a-descriptions-item label="预约编号">{{ currentAppointment?.id }}</a-descriptions-item>
        <a-descriptions-item label="预约状态">
          <a-tag :color="getStatusColor(currentAppointment?.status)">
            {{ getStatusText(currentAppointment?.status) }}
          </a-tag>
        </a-descriptions-item>
        <a-descriptions-item label="预约日期">{{ formatDate(currentAppointment?.appointmentDate) }}</a-descriptions-item>
        <a-descriptions-item label="就诊时间">{{ currentAppointment?.timeSlot }}</a-descriptions-item>
        <a-descriptions-item label="科室">{{ currentAppointment?.departmentName }}</a-descriptions-item>
        <a-descriptions-item label="医生">{{ currentAppointment?.doctorName }} {{ currentAppointment?.doctorTitle }}</a-descriptions-item>
        <a-descriptions-item label="病情描述">{{ currentAppointment?.description || '无' }}</a-descriptions-item>
        <a-descriptions-item label="创建时间">{{ formatDateTime(currentAppointment?.createdAt) }}</a-descriptions-item>
        <a-descriptions-item label="最后更新时间" v-if="currentAppointment?.updatedAt">
          {{ formatDateTime(currentAppointment?.updatedAt) }}
        </a-descriptions-item>
        <a-descriptions-item label="取消原因" v-if="currentAppointment?.status === 'cancelled'">
          {{ currentAppointment?.cancelReason || '无' }}
        </a-descriptions-item>
      </a-descriptions>
    </a-modal>

    <!-- 取消预约模态框 -->
    <a-modal
      v-model:visible="cancelVisible"
      title="取消预约"
      ok-text="确认取消"
      cancel-text="再想想"
      @ok="handleCancelAppointment"
    >
      <a-form layout="vertical">
        <a-form-item label="取消原因" required>
          <a-textarea
            v-model:value="cancelReason"
            placeholder="请输入取消原因"
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
import { getAppoint, getLoginInfo, updateAppointStatus } from '@/api/api'

// 表格列定义
const columns = [
  {
    title: '预约编号',
    dataIndex: 'id',
    key: 'id',
    width: 120
  },
  {
    title: '预约日期',
    dataIndex: 'appointmentDate',
    key: 'appointmentDate',
    width: 120,
    customRender: ({ text }) => formatDate(text)
  },
  {
    title: '就诊时间',
    dataIndex: 'timeSlot',
    key: 'timeSlot',
    width: 120
  },
  {
    title: '科室',
    dataIndex: 'departmentName',
    key: 'departmentName'
  },
  {
    title: '医生',
    dataIndex: 'doctorName',
    key: 'doctorName',
    customRender: ({ record }) => `${record.doctorName} ${record.doctorTitle}`
  },
  {
    title: '状态',
    dataIndex: 'status',
    key: 'status',
    width: 100
  },
  {
    title: '操作',
    key: 'action',
    width: 250
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
  dateRange: []
})

// 数据状态
const appointments = ref([])
const loading = ref(false)
const detailVisible = ref(false)
const cancelVisible = ref(false)
const currentAppointment = ref(null)
const cancelReason = ref('')
const patientId = ref(null)

// 初始化加载数据
onMounted(async () => {
  try {
    const loginInfo = await getLoginInfo()
    patientId.value = loginInfo.data.user_id
    await fetchAppointments()
  } catch (error) {
    message.error('获取登录信息失败')
  }
})

// 获取预约列表
const fetchAppointments = async () => {
  try {
    loading.value = true
    const params = {
      patient_id: patientId.value,
      appointment_status: getStatusCode(filterForm.value.status)
    }

    // 处理日期范围筛选
    if (filterForm.value.dateRange && filterForm.value.dateRange.length === 2) {
      params.start_date = dayjs(filterForm.value.dateRange[0]).format('YYYY-MM-DD')
      params.end_date = dayjs(filterForm.value.dateRange[1]).format('YYYY-MM-DD')
    }

    const response = await getAppoint(params)
    console.log(response.data)
    if (response.data.code === 200) {
      appointments.value = response.data.data.map(item => ({
        id: item.app_if.AppointmentID,
        appointmentDate: item.app_if.AppointmentDateTime.split(' ')[0],
        timeSlot: item.app_if.AppointmentDateTime.split(' ')[1],
        departmentName: item.doc_if.DoctorDepartmentName,
        doctorName: item.doc_if.DoctorName,
        doctorTitle: item.doc_if.DoctorTitle,
        status: getStatusTextFromCode(item.app_if.AppointmentStatus),
        description: '无', // 原数据未提供病情描述，可根据实际情况修改
        createdAt: '', // 原数据未提供创建时间，可根据实际情况修改
        updatedAt: '', // 原数据未提供更新时间，可根据实际情况修改
        cancelReason: '' // 原数据未提供取消原因，可根据实际情况修改
      }))

      // 添加排序逻辑，按照预约日期降序排列（最新的在前面）
      appointments.value.sort((a, b) => {
        return dayjs(b.appointmentDate).isAfter(dayjs(a.appointmentDate)) ? 1 : -1;
      });

      pagination.value.total = appointments.value.length
    } else {
      message.error('获取预约列表失败')
    }
  } catch (error) {
    message.error('获取预约列表失败' + error.message)
  } finally {
    loading.value = false
  }
}

// 格式化日期
const formatDate = date => {
  return date ? dayjs(date).format('YYYY-MM-DD') : '-'
}

// 格式化日期时间
const formatDateTime = datetime => {
  return datetime ? dayjs(datetime).format('YYYY-MM-DD HH:mm:ss') : '-'
}

// 获取状态文本
const getStatusText = status => {
  const map = {
    pending: '已预约',
    in_progress: '正在进行',
    completed: '已结束',
    missed: '过号',
    checked_in: '患者已签到'
  }
  return map[status] || status
}

// 获取状态颜色
const getStatusColor = status => {
  const map = {
    pending: 'blue',
    in_progress: 'orange',
    completed: 'green',
    missed: 'red',
    checked_in: 'purple'
  }
  return map[status] || 'default'
}

// 获取状态码
const getStatusCode = status => {
  const map = {
    pending: 0,
    in_progress: 1,
    completed: 2,
    missed: 3,
    checked_in: 4
  }
  return map[status]
}

// 根据状态码获取状态文本
const getStatusTextFromCode = code => {
  const map = {
    0: 'pending',
    1: 'in_progress',
    2: 'completed',
    3: 'missed',
    4: 'checked_in'
  }
  return map[code]
}

// 禁用日期
const disabledDate = current => {
  return current && current > dayjs().endOf('day')
}

// 处理查询
const handleSearch = () => {
  pagination.value.current = 1
  fetchAppointments()
}

// 重置查询
const resetSearch = () => {
  filterForm.value = {
    status: undefined,
    dateRange: []
  }
  handleSearch()
}

// 处理表格变化
const handleTableChange = (pag, filters, sorter) => {
  pagination.value.current = pag.current
  pagination.value.pageSize = pag.pageSize
  fetchAppointments()
}

// 显示详情
const showDetail = record => {
  currentAppointment.value = record
  detailVisible.value = true
}

// 显示取消模态框
const showCancelModal = record => {
  currentAppointment.value = record
  cancelReason.value = ''
  cancelVisible.value = true
}

// 处理取消预约
const handleCancelAppointment = async () => {
  if (!cancelReason.value.trim()) {
    message.warning('请输入取消原因')
    return
  }
  
  try {
    loading.value = true
    const formData = new FormData()
    formData.append('appointment_id', currentAppointment.value.id)
    formData.append('new_status', getStatusCode('completed'))
    const response = await updateAppointStatus(formData)
    if (response.data.code === 200) {
      // 更新本地数据
      const index = appointments.value.findIndex(item => item.id === currentAppointment.value.id)
      if (index !== -1) {
        appointments.value[index] = {
          ...appointments.value[index],
          status: 'completed',
          updatedAt: dayjs().format('YYYY-MM-DD HH:mm:ss'),
          cancelReason: cancelReason.value
        }
      }
      message.success('预约已取消')
      cancelVisible.value = false
    } else {
      message.error('取消预约失败')
    }
  } catch (error) {
    message.error('取消预约失败' + error.message)
  } finally {
    loading.value = false
  }
}

// 处理签到
const handleCheckIn = async (record) => {
  if (!isSameDay(record.appointmentDate)) {
    message.warning('只有今天的挂号可以签到')
    return
  }
  try {
    loading.value = true
    const formData = new FormData()
    formData.append('appointment_id', record.id)
    formData.append('new_status', getStatusCode('checked_in'))
    const response = await updateAppointStatus(formData)
    if (response.data.code === 200) {
      // 更新本地数据
      const index = appointments.value.findIndex(item => item.id === record.id)
      if (index !== -1) {
        appointments.value[index] = {
          ...appointments.value[index],
          status: 'checked_in',
          updatedAt: dayjs().format('YYYY-MM-DD HH:mm:ss')
        }
      }
      message.success('签到成功')
    } else {
      message.error('签到失败')
    }
  } catch (error) {
    message.error('签到失败' + error.message)
  } finally {
    loading.value = false
  }
}

// 判断是否是同一天
const isSameDay = (date) => {
  return dayjs(date).isSame(dayjs(), 'day')
}
</script>

<style scoped>
.appointment-container {
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