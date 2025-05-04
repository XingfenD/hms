<template>
    <div class="call-patient-container">
        <a-card title="患者呼叫系统" :bordered="false">
            <div class="call-controls">
                <a-space>
                    <a-button type="primary" size="large" @click="callNextPatient" :loading="calling">
                        <template #icon><NotificationOutlined /></template>
                        呼叫下一位患者
                    </a-button>
                    <a-button size="large" @click="skipCurrentPatient" :disabled="!currentPatient">
                        <template #icon><StepForwardOutlined /></template>
                        跳过当前患者
                    </a-button>
                </a-space>
            </div>
  
            <!-- 当前就诊患者 -->
            <div class="current-patient" v-if="currentPatient">
                <h3>当前就诊患者</h3>
                <a-descriptions bordered :column="2" size="middle">
                    <a-descriptions-item label="患者姓名">{{ currentPatient.name }}</a-descriptions-item>
                    <a-descriptions-item label="患者性别">{{ currentPatient.gender === 'male' ? '男' : '女' }}</a-descriptions-item>
                    <a-descriptions-item label="患者年龄">{{ currentPatient.age }}岁</a-descriptions-item>
                    <a-descriptions-item label="挂号时间">{{ formatTime(currentPatient.registerTime) }}</a-descriptions-item>
                    <a-descriptions-item label="等待时间">{{ calculateWaitTime(currentPatient.registerTime) }}分钟</a-descriptions-item>
                    <a-descriptions-item label="主诉">{{ currentPatient.symptoms || '暂无' }}</a-descriptions-item>
                </a-descriptions>
                <div class="patient-actions">
                    <a-button type="primary" @click="startConsultation">开始就诊</a-button>
                    <a-button @click="markAsNoShow">标记为过号</a-button>
                </div>
            </div>
  
            <!-- 排队患者列表 -->
            <div class="waiting-list">
                <h3>排队患者列表 ({{ waitingPatients.length }}人)</h3>
                <a-table
                    :columns="waitingColumns"
                    :data-source="waitingPatients"
                    :row-key="record => record.id"
                    :pagination="false"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'waitTime'">
                            {{ calculateWaitTime(record.registerTime) }}分钟
                        </template>
                        <template v-else-if="column.key === 'status'">
                            <a-tag :color="getStatusColor(record.appointmentStatus)">
                                {{ getStatusText(record.appointmentStatus) }}
                            </a-tag>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-card>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { message } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import { NotificationOutlined, StepForwardOutlined } from '@ant-design/icons-vue';
  import { getAppoint, getLoginInfo, getSchedules, updateAppointStatus } from '@/api/api';
  
  // 排队列表列定义
  const waitingColumns = [
    {
        title: '序号',
        dataIndex: 'queueNumber',
        key: 'queueNumber',
        width: 80
    },
    {
        title: '患者姓名',
        dataIndex: 'name',
        key: 'name',
        width: 120
    },
    {
        title: '性别',
        dataIndex: 'gender',
        key: 'gender',
        width: 80,
        customRender: ({ text }) => text === 'male' ? '男' : '女'
    },
    {
        title: '年龄',
        dataIndex: 'age',
        key: 'age',
        width: 80
    },
    {
        title: '挂号时间',
        dataIndex: 'registerTime',
        key: 'registerTime',
        customRender: ({ text }) => formatTime(text)
    },
    {
        title: '等待时间',
        key: 'waitTime',
        width: 120
    },
    {
        title: '状态',
        key: 'status',
        width: 100
    }
  ];
  
  // 数据状态
  const calling = ref(false);
  const currentPatient = ref(null);
  const waitingPatients = ref([]);
  const doctorId = ref(null);
  const currentSchedule = ref(null);
  
  // 初始化数据
  onMounted(async () => {
    try {
        const loginInfo = await getLoginInfo();
        doctorId.value = loginInfo.data.data.doctor_id;
        console.log(loginInfo.data)
        const today = dayjs().format('YYYY-MM-DD');
        const schedules = await getSchedules({ start_date: today, end_date: today });
        console.log(schedules.data)
        currentSchedule.value = schedules.data.data.find(schedule => schedule.doctor_id === doctorId.value);
        if (currentSchedule.value) {
            await fetchWaitingPatients();
        }
    } catch (error) {
        message.error('初始化数据失败：' + error.message);
    }
  });
  
  // 获取排队患者列表
  const fetchWaitingPatients = async () => {
    try {
        const params = {
            doctor_id: doctorId.value
        };
        const response = await getAppoint(params);
        console.log(response.data)
        if (response.data.code === 200) {
            const today = dayjs().startOf('day');
            waitingPatients.value = response.data.data
             .filter(item => dayjs(item.app_if.AppointmentDateTime).isSame(today, 'day'))
             .map((item, index) => {
                return {
                    id: item.app_if.AppointmentID,
                    queueNumber: index + 1,
                    name: item.pat_if.PatientName,
                    gender: item.pat_if.PatientGender === 0 ? 'female' : 'male',
                    age: item.pat_if.PatientAge,
                    registerTime: item.app_if.AppointmentDateTime,
                    symptoms: '暂无',
                    appointmentStatus: item.app_if.AppointmentStatus
                };
            }).sort((a, b) => a.registerTime.localeCompare(b.registerTime));
            currentPatient.value = waitingPatients.value.find(p => p.appointmentStatus === 1);
        } else {
            message.error('获取排队患者列表失败');
        }
    } catch (error) {
        message.error('获取排队患者列表失败：' + error.message);
    }
  };
  
  // 方法
  const formatTime = datetime => {
    return datetime ? dayjs(datetime).format('HH:mm:ss') : '-';
  };
  
  const calculateWaitTime = registerTime => {
    if (!registerTime) return 0;
    const now = dayjs();
    const register = dayjs(registerTime);
    return Math.floor(now.diff(register, 'minute'));
  };
  
  const getStatusText = status => {
    const map = {
        0: '已预约',
        1: '正在进行',
        2: '已结束',
        3: '过号',
        4: '患者已签到'
    };
    return map[status] || '未知状态';
  };
  
  const getStatusColor = status => {
    const map = {
        0: 'blue',
        1: 'green',
        2: 'gray',
        3: 'red',
        4: 'orange'
    };
    return map[status] || 'default';
  };
  
  const callNextPatient = async () => {
    calling.value = true;
    try {
        const nextPatient = waitingPatients.value.find(p => p.appointmentStatus === 4);
        if (nextPatient) {
            nextPatient.appointmentStatus = 1;
            currentPatient.value = nextPatient;
            message.success(`已呼叫患者 ${nextPatient.name}`);
            await updateAppointmentStatus(nextPatient.id, 1); // 呼叫后将状态更新为正在进行
        } else {
            message.info('没有已签到等待中的患者了');
        }
    } catch (error) {
        message.error('呼叫患者失败：' + error.message);
    } finally {
        calling.value = false;
    }
  };
  
  const skipCurrentPatient = async () => {
    if (currentPatient.value) {
        currentPatient.value.appointmentStatus = 3;
        message.warning(`已标记患者 ${currentPatient.value.name} 为过号`);
        await updateAppointmentStatus(currentPatient.value.id, 3); // 标记为过号
        currentPatient.value = null;
    }
  };
  
  const startConsultation = async () => {
    if (currentPatient.value) {
        currentPatient.value.appointmentStatus = 1;
        message.success(`开始为患者 ${currentPatient.value.name} 就诊`);
        await updateAppointmentStatus(currentPatient.value.id, 1); // 开始就诊，更新状态为正在进行
        // 这里可以跳转到就诊页面
    }
  };
  
  const markAsNoShow = async () => {
    if (currentPatient.value) {
        currentPatient.value.appointmentStatus = 3;
        message.warning(`已标记患者 ${currentPatient.value.name} 为过号`);
        await updateAppointmentStatus(currentPatient.value.id, 3); // 标记为过号
        currentPatient.value = null;
    }
  };
  
  const updateAppointmentStatus = async (appointmentId, newStatus) => {
    try {
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('new_status', newStatus);
        const response = await updateAppointStatus(formData);
        if (response.data.code!== 200) {
            message.error('更新挂号状态失败');
        }
    } catch (error) {
        message.error('更新挂号状态失败：' + error.message);
    }
  };
  </script>
  
  <style scoped>
  .call-patient-container {
    padding: 24px;
  }
  
  .call-controls {
    margin-bottom: 24px;
  }
  
  .current-patient {
    margin-bottom: 24px;
    padding: 16px;
    border: 1px solid #f0f0f0;
    border-radius: 4px;
  }
  
  .current-patient h3 {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  
  .patient-actions {
    margin-top: 16px;
    display: flex;
    justify-content: flex-end;
    gap: 16px;
  }
  
  .waiting-list h3 {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  </style>    