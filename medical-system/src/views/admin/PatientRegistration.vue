<template>
    <a-layout class="registration-management">
        <!-- 顶部导航 -->
        <a-page-header
            title="患者挂号管理"
            sub-title="查询和管理患者挂号记录"
            class="page-header"
            @back="() => router.push('/admin')"
        >
            <template #backIcon>
                <arrow-left-outlined />
            </template>
            <template #breadcrumb>
                <a-breadcrumb>
                    <a-breadcrumb-item>
                        <router-link to="/admin">管理员面板</router-link>
                    </a-breadcrumb-item>
                    <a-breadcrumb-item>患者挂号</a-breadcrumb-item>
                </a-breadcrumb>
            </template>
            <template #extra>
                <a-button type="primary" @click="showCreateModal">
                    <template #icon><plus-outlined /></template>
                    新增挂号
                </a-button>
            </template>
        </a-page-header>

        <!-- 主内容区 -->
        <a-layout-content class="content">
            <div class="container">
                <!-- 查询表单 -->
                <a-card title="挂号查询" class="search-card">
                    <a-form layout="inline" @finish="handleSearch">
                        <a-form-item label="患者ID">
                            <a-input
                                v-model:value="searchForm.patientId"
                                placeholder="请输入患者ID"
                                style="width: 200px"
                            />
                        </a-form-item>
                        <a-form-item label="医生ID">
                            <a-input
                                v-model:value="searchForm.doctorId"
                                placeholder="请输入医生ID"
                                style="width: 200px"
                            />
                        </a-form-item>
                        <a-form-item label="科室">
                            <a-select
                                v-model:value="searchForm.departmentId"
                                placeholder="选择科室"
                                style="width: 150px"
                            >
                                <a-select-option value="">全部科室</a-select-option>
                                <a-select-option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                        <a-form-item label="日期范围">
                            <a-range-picker
                                v-model:value="searchForm.dateRange"
                                style="width: 220px"
                            />
                        </a-form-item>
                        <a-form-item label="状态">
                            <a-select
                                v-model:value="searchForm.status"
                                placeholder="选择状态"
                                style="width: 150px"
                            >
                                <a-select-option value="">全部状态</a-select-option>
                                <a-select-option value="0">0 - 已预约</a-select-option>
                                <a-select-option value="1">1 - 正在进行</a-select-option>
                                <a-select-option value="2">2 - 已结束</a-select-option>
                                <a-select-option value="3">3 - 过号</a-select-option>
                                <a-select-option value="4">4 - 患者已签到</a-select-option>
                            </a-select>
                        </a-form-item>
                        <a-form-item>
                            <a-space>
                                <a-button type="primary" html-type="submit">
                                    <template #icon><search-outlined /></template>
                                    查询
                                </a-button>
                                <a-button @click="resetSearch">
                                    <template #icon><redo-outlined /></template>
                                    重置
                                </a-button>
                            </a-space>
                        </a-form-item>
                    </a-form>
                </a-card>

                <!-- 挂号记录表格 -->
                <a-card title="挂号记录" class="table-card">
                    <template #extra>
                        <a-button type="link" @click="exportToExcel">
                            <template #icon><download-outlined /></template>
                            导出Excel
                        </a-button>
                    </template>

                    <a-table
                        :columns="columns"
                        :data-source="filteredAppointments"
                        :pagination="pagination"
                        :loading="loading"
                        row-key="id"
                        bordered
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'patientInfo'">
                                <router-link :to="`/patient/detail/${record.patientId}`">
                                    {{ record.patientName }} (ID: {{ record.patientId }})
                                </router-link>
                                <div class="patient-meta">
                                    <span>{{ record.patientGender }} | {{ record.patientAge }}岁</span>
                                </div>
                            </template>

                            <template v-if="column.key === 'doctorInfo'">
                                <router-link :to="`/doctor/detail/${record.doctorId}`">
                                    {{ record.doctorName }} (ID: {{ record.doctorId }})
                                </router-link>
                                <div class="doctor-meta">
                                    <span>{{ record.departmentName }} | {{ record.doctorTitle }}</span>
                                </div>
                            </template>

                            <template v-if="column.key === 'appointmentTime'">
                                <div class="time-display">
                                    <div>{{ record.appointmentDate }}</div>
                                    <div>{{ record.appointmentTime }}</div>
                                </div>
                            </template>

                            <template v-if="column.key === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ getStatusText(record.status) }}
                                </a-tag>
                            </template>

                            <template v-if="column.key === 'actions'">
                                <a-space>
                                    <a-button size="small" @click="viewDetails(record)">
                                        <template #icon><eye-outlined /></template>
                                        详情
                                    </a-button>
                                    <a-button
                                        v-if="record.status === 0"
                                        size="small"
                                        @click="startAppointment(record)"
                                    >
                                        <template #icon><play-circle-outlined /></template>
                                        开始
                                    </a-button>
                                    <a-button
                                        v-if="record.status === 1"
                                        size="small"
                                        @click="endAppointment(record)"
                                    >
                                        <template #icon><stop-outlined /></template>
                                        结束
                                    </a-button>
                                    <a-popconfirm
                                        v-if="record.status === 0"
                                        title="确定要取消这个挂号吗？"
                                        ok-text="确定"
                                        cancel-text="取消"
                                        @confirm="cancelAppointment(record.id)"
                                    >
                                        <a-button size="small" danger>
                                            <template #icon><close-outlined /></template>
                                            取消
                                        </a-button>
                                    </a-popconfirm>
                                </a-space>
                            </template>
                        </template>
                    </a-table>
                </a-card>
            </div>
        </a-layout-content>

        <!-- 新增挂号模态框 -->
        <a-modal
            v-model:visible="createModalVisible"
            title="新增患者挂号"
            width="800px"
            :mask-closable="false"
            :destroy-on-close="true"
            @ok="handleCreate"
            @cancel="resetCreateForm"
        >
            <a-form
                ref="createFormRef"
                :model="createForm"
                :rules="createRules"
                layout="vertical"
            >
                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="选择患者" name="patientId">
                            <a-select
                                v-model:value="createForm.patientId"
                                show-search
                                placeholder="搜索患者"
                                :filter-option="filterPatientOption"
                                @search="handlePatientSearch"
                            >
                                <a-select-option v-for="patient in filteredPatients" :key="patient.id" :value="patient.id">
                                    {{ patient.name }} (ID: {{ patient.id }}) | {{ patient.gender }} | {{ patient.age }}岁
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="选择科室" name="departmentId">
                            <a-select
                                v-model:value="createForm.departmentId"
                                placeholder="选择科室"
                                @change="fetchDepartmentScheduledDoctors"
                            >
                                <a-select-option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="选择医生" name="doctorId">
                            <a-select
                                v-model:value="createForm.doctorId"
                                placeholder="选择医生"
                                :disabled="!createForm.departmentId ||!createForm.date"
                                @change="handleDoctorSelect"
                            >
                                <a-select-option v-for="doctor in departmentScheduledDoctors" :key="doctor.DoctorOwnID" :value="doctor.DoctorOwnID">
                                    {{ doctor.FullName }} ({{ doctor.title }})
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="挂号类型" name="type">
                            <a-select v-model:value="createForm.type" placeholder="选择挂号类型">
                                <a-select-option value="普通号">普通号</a-select-option>
                                <a-select-option value="专家号">专家号</a-select-option>
                                <a-select-option value="急诊">急诊</a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="24">
                    <a-col :span="12">
                        <a-form-item label="预约日期" name="date">
                            <a-date-picker
                                v-model:value="createForm.date"
                                style="width: 100%"
                                :disabled-date="disabledDate"
                                @change="fetchDepartmentScheduledDoctors"
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="预约时间" name="time">
                            <a-select
                                v-model:value="createForm.time"
                                placeholder="选择时间"
                                :disabled="!createForm.date ||!createForm.doctorId"
                            >
                                <a-select-option v-for="time in availableTimes" :key="time" :value="time">
                                    {{ time }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-form-item label="备注" name="remark">
                    <a-textarea
                        v-model:value="createForm.remark"
                        placeholder="请输入备注信息"
                        :rows="3"
                    />
                </a-form-item>
            </a-form>
        </a-modal>

        <!-- 挂号详情模态框 -->
        <a-modal
            v-model:visible="detailModalVisible"
            :title="`挂号详情 (ID: ${currentAppointment?.id || ''})`"
            width="700px"
            :footer="null"
        >
            <a-descriptions bordered :column="2" v-if="currentAppointment">
                <a-descriptions-item label="患者信息">
                    {{ currentAppointment.patientName }} (ID: {{ currentAppointment.patientId }})
                    <div>{{ currentAppointment.patientGender }} | {{ currentAppointment.patientAge }}岁</div>
                    <div>联系电话: {{ currentAppointment.patientPhone }}</div>
                </a-descriptions-item>
                <a-descriptions-item label="医生信息">
                    {{ currentAppointment.doctorName }} (ID: {{ currentAppointment.doctorId }})
                    <div>{{ currentAppointment.departmentName }} | {{ currentAppointment.doctorTitle }}</div>
                </a-descriptions-item>
                <a-descriptions-item label="预约时间">
                    {{ currentAppointment.appointmentDate }} {{ currentAppointment.appointmentTime }}
                </a-descriptions-item>
                <a-descriptions-item label="挂号类型">
                    {{ currentAppointment.type }}
                </a-descriptions-item>
                <a-descriptions-item label="当前状态">
                    <a-tag :color="getStatusColor(currentAppointment.status)">
                        {{ getStatusText(currentAppointment.status) }}
                    </a-tag>
                </a-descriptions-item>
                <a-descriptions-item label="创建时间">
                    {{ currentAppointment.createdAt }}
                </a-descriptions-item>
                <a-descriptions-item label="备注" :span="2">
                    {{ currentAppointment.remark || '无' }}
                </a-descriptions-item>
            </a-descriptions>
        </a-modal>
    </a-layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
    ArrowLeftOutlined,
    PlusOutlined,
    SearchOutlined,
    RedoOutlined,
    DownloadOutlined,
    EyeOutlined,
    CheckOutlined,
    CloseOutlined,
    PlayCircleOutlined,
    StopOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import dayjs from 'dayjs';
import { getAppoint, addAppoint, getSchedules, getDoctorsByDepartment, get_users_data, updateAppointStatus } from '@/api/api';

const router = useRouter();

// 科室数据
const departments = ref([
    { id: 1, name: '内科' },
    { id: 2, name: '外科' },
    { id: 3, name: '儿科' },
    { id: 4, name: '妇产科' },
    { id: 5, name: '眼科' }
]);

// 创建科室id到科室名的映射对象
const departmentIdToNameMap = computed(() => {
    const map = {};
    departments.value.forEach(dept => {
        map[dept.id] = dept.name;
    });
    return map;
});

// 患者数据
const patients = ref([]);
const filteredPatients = ref([]);
const patientSearchText = ref('');

// 医生数据
const doctors = ref([]);
const departmentScheduledDoctors = ref([]);

// 排班数据
const schedules = ref([]);

// 挂号记录数据
const appointments = ref([]);
const loading = ref(false);
const currentAppointment = ref(null);

// 搜索表单
const searchForm = reactive({
    patientId: '',
    doctorId: '',
    departmentId: '',
    dateRange: [],
    status: ''
});

// 新增挂号表单
const createFormRef = ref();
const createForm = reactive({
    patientId: null,
    departmentId: null,
    doctorId: null,
    type: '普通号',
    date: null,
    time: null,
    remark: ''
});

const createRules = {
    patientId: [{ required: true, message: '请选择患者' }],
    departmentId: [{ required: true, message: '请选择科室' }],
    doctorId: [{ required: true, message: '请选择医生' }],
    type: [{ required: true, message: '请选择挂号类型' }],
    date: [{ required: true, message: '请选择预约日期' }],
    time: [{ required: true, message: '请选择预约时间' }]
};

// 分页配置
const pagination = reactive({
    current: 1,
    pageSize: 10,
    total: 0,
    showSizeChanger: true,
    pageSizeOptions: ['10', '20', '50', '100'],
    showTotal: total => `共 ${total} 条记录`
});

// 模态框状态
const createModalVisible = ref(false);
const detailModalVisible = ref(false);
const availableTimes = ref([]);

// 表格列定义
const columns = [
    {
        title: '挂号ID',
        dataIndex: 'id',
        key: 'id',
        width: 100
    },
    {
        title: '患者信息',
        key: 'patientInfo',
        width: 200
    },
    {
        title: '医生信息',
        key: 'doctorInfo',
        width: 200
    },
    {
        title: '挂号类型',
        dataIndex: 'type',
        key: 'type',
        width: 120,
        filters: [
            { text: '普通号', value: '普通号' },
            { text: '专家号', value: '专家号' },
            { text: '急诊', value: '急诊' }
        ],
        onFilter: (value, record) => record.type === value
    },
    {
        title: '预约时间',
        key: 'appointmentTime',
        width: 180,
        sorter: (a, b) => {
            const dateA = new Date(`${a.appointmentDate} ${a.appointmentTime}`);
            const dateB = new Date(`${b.appointmentDate} ${b.appointmentTime}`);
            return dateA - dateB;
        }
    },
    {
        title: '状态',
        key: 'status',
        width: 120,
        filters: [
            { text: '0 - 已预约', value: '0' },
            { text: '1 - 正在进行', value: '1' },
            { text: '2 - 已结束', value: '2' },
            { text: '3 - 过号', value: '3' },
            { text: '4 - 患者已签到', value: '4' }
        ],
        onFilter: (value, record) => record.status.toString() === value
    },
    {
        title: '操作',
        key: 'actions',
        width: 180
    }
];

// 计算属性
const filteredAppointments = computed(() => {
    let result = [...appointments.value];
    
    if (searchForm.patientId) {
        result = result.filter(item => 
            item.patientId.toString().includes(searchForm.patientId)
        );
    }
    
    if (searchForm.doctorId) {
        result = result.filter(item => 
            item.doctorId.toString().includes(searchForm.doctorId)
        );
    }
    
    if (searchForm.departmentId) {
        result = result.filter(item => 
            item.departmentId === searchForm.departmentId
        );
    }
    
    if (searchForm.dateRange && searchForm.dateRange.length === 2) {
        const [start, end] = searchForm.dateRange;
        result = result.filter(item => {
            const date = dayjs(item.appointmentDate);
            return date.isAfter(start) && date.isBefore(end);
        });
    }
    
    if (searchForm.status) {
        result = result.filter(item => item.status.toString() === searchForm.status);
    }
    
    pagination.total = result.length;
    return result;
});

// 方法
const getStatusColor = (status) => {
    switch (status) {
        case 0: return 'blue';
        case 1: return 'orange';
        case 2: return 'green';
        case 3: return 'red';
        case 4: return 'cyan';
        default: return 'default';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 0: return '已预约';
        case 1: return '正在进行';
        case 2: return '已结束';
        case 3: return '过号';
        case 4: return '患者已签到';
        default: return '未知状态';
    }
};

const disabledDate = (current) => {
    return current && current < dayjs().startOf('day');
};

const fetchAppointments = async () => {
    try {
        loading.value = true;
        const params = {
            patient_id: searchForm.patientId,
            doctor_id: searchForm.doctorId,
            ap_sc_id: null, 
            appointment_status: null 
        };

        const response = await getAppoint(params);
        console.log(response.data, response.data.code);
        if (response.data.code === 200) {
            appointments.value = response.data.data.map(item => {
                return {
                    id: item.app_if.AppointmentID,
                    patientId: item.pat_if.PatientId,
                    patientName: item.pat_if.PatientName,
                    patientGender: item.pat_if.PatientGender === 0? '女' : '男',
                    patientAge: item.pat_if.PatientAge,
                    doctorId: item.doc_if.DoctorID,
                    doctorName: item.doc_if.DoctorName,
                    doctorTitle: item.doc_if.DoctorTitle,
                    departmentId: null, 
                    departmentName: item.doc_if.DoctorDepartmentName,
                    type: null, 
                    appointmentDate: dayjs(item.app_if.AppointmentDateTime).format('YYYY-MM-DD'),
                    appointmentTime: dayjs(item.app_if.AppointmentDateTime).format('HH:mm'),
                    status: item.app_if.AppointmentStatus,
                    createdAt: null, 
                    remark: null 
                };
            });
        } else {
            message.error('获取挂号记录失败');
        }
    } catch (error) {
        message.error('获取挂号记录失败' + error.message);
    } finally {
        loading.value = false;
    }
};

const fetchPatients = async () => {
    try {
        const response = await get_users_data();
        if (response.data.code === 200) {
            patients.value = response.data.data.patients.map(patient => ({
                id: patient.PatientID,
                name: patient.FullName,
                gender: patient.Gender === 0? '女' : '男',
                age: patient.Age,
                phone: patient.UserCell
            }));
            filteredPatients.value = [...patients.value];
        } else {
            message.error('获取患者列表失败');
        }
    } catch (error) {
        message.error('获取患者列表失败' + error.message);
    }
};

const fetchDoctors = async () => {
    try {
        const response = await get_users_data();
        if (response.data.code === 200) {
            doctors.value = response.data.data.doctors.map(doctor => ({
                id: doctor.DoctorID,
                name: doctor.FullName,
                title: '', 
                departmentId: departments.value.find(dept => dept.name === doctor.Department)?.id,
                phone: doctor.UserCell
            }));
        } else {
            message.error('获取医生列表失败');
        }
    } catch (error) {
        message.error('获取医生列表失败' + error.message);
    }
};

const fetchDepartmentScheduledDoctors = async () => {
    if (!createForm.departmentId ||!createForm.date) return;
    
    try {
        const params = {
            start_date: createForm.date.format('YYYY-MM-DD'),
            end_date: createForm.date.format('YYYY-MM-DD')
        };
        const schedulesResponse = await getSchedules(params);
        console.log(schedulesResponse.data.data);
        if (schedulesResponse.data.code === 200) {
            schedules.value = schedulesResponse.data.data;
        } else {
            message.error('获取排班列表失败');
            return;
        }

        const doctorsResponse = await getDoctorsByDepartment(createForm.departmentId);
        // console.log(doctorsResponse.data);
        if (doctorsResponse.data.code === 200) {
            doctors.value = doctorsResponse.data.data;
        } else {
            message.error('获取科室医生列表失败');
            return;
        }
        console.log(schedules.value);
        const selectedDate = createForm.date.format('YYYY-MM-DD');
        const scheduledDoctorIds = schedules.value.filter(schedule => 
            schedule.Department === departmentIdToNameMap.value[createForm.departmentId] && schedule.ScheduleDate === selectedDate
        ).map(schedule => schedule.doctor_id);
        
        console.log(doctors.value);
        departmentScheduledDoctors.value = doctors.value.filter(
            doctor => scheduledDoctorIds.includes(doctor.DoctorOwnID)
        );
        // console.log(scheduledDoctorIds.value, departmentScheduledDoctors.value)
    } catch (error) {
        message.error('获取科室排班医生失败');
    }
};

const fetchAvailableTimes = async () => {
    if (!createForm.date ||!createForm.doctorId) return;
    
    try {
        const selectedDate = createForm.date.format('YYYY-MM-DD');
        const schedule = schedules.value.find(schedule => 
            schedule.doctor_id === createForm.doctorId && schedule.ScheduleDate === selectedDate
        );
        console.log(schedule)
        if (schedule) {
            const startTime = schedule.StartTime;
            const endTime = schedule.EndTime;
            const start = dayjs(selectedDate + ' ' + startTime);
            const end = startTime > endTime? dayjs(selectedDate + ' ' + endTime).add(1, 'day') : dayjs(selectedDate + ' ' + endTime);
            const interval = 30; // 30分钟间隔
            const times = [];
            let currentTime = start;
            while (currentTime.isBefore(end)) {
                times.push(currentTime.format('HH:mm'));
                currentTime = currentTime.add(interval, 'minutes');
            }
            availableTimes.value = times;
        } else {
            availableTimes.value = [];
        }
    } catch (error) {
        message.error('获取可用时间段失败');
    }
};

const handleSearch = () => {
    pagination.current = 1;
    fetchAppointments();
};

const resetSearch = () => {
    Object.assign(searchForm, {
        patientId: '',
        doctorId: '',
        departmentId: '',
        dateRange: [],
        status: ''
    });
    fetchAppointments();
};

const showCreateModal = () => {
    createModalVisible.value = true;
    fetchPatients();
    fetchDoctors();
};

const resetCreateForm = () => {
    createFormRef.value?.resetFields();
    Object.assign(createForm, {
        patientId: null,
        departmentId: null,
        doctorId: null,
        type: '普通号',
        date: null,
        time: null,
        remark: ''
    });
    departmentScheduledDoctors.value = [];
    availableTimes.value = [];
};

const handleCreate = async () => {
    try {
        await createFormRef.value.validate();

        const selectedDate = createForm.date.format('YYYY-MM-DD');
        const selectedSchedule = schedules.value.find(schedule => 
            schedule.doctor_id === createForm.doctorId && 
            schedule.ScheduleDate === selectedDate && 
            // 这里假设排班数据中有一个包含可用时间的字段，根据实际情况修改
            // schedule.times.includes(createForm.time) 
            true // 暂时简单处理，实际需要根据具体情况判断
        );

        if (!selectedSchedule) {
            message.error('未找到对应的排班信息');
            return;
        }
        // console.log(selectedSchedule);
        const formData = new FormData();
        formData.append('patient_id', createForm.patientId);
        formData.append('schedule_id', selectedSchedule.schedule_id); // 使用找到的排班ID
        formData.append('appointment_date', createForm.date.format('YYYY-MM-DD'));
        if (createForm.time) {
            formData.append('appointment_time', createForm.time);
        }
        console.log(formData.get('schedule_id'))
        const response = await addAppoint(formData);
        console.log(response.data)
        if (response.data.code === 200) {
            const patient = patients.value.find(p => p.id === createForm.patientId);
            const doctor = doctors.value.find(d => d.id === createForm.doctorId);
            const department = departments.value.find(d => d.id === createForm.departmentId);

            const newAppointment = {
                id: Math.max(...appointments.value.map(a => a.id), 0) + 1,
                patientId: createForm.patientId,
                patientName: patient?.name || '',
                patientGender: patient?.gender || '',
                patientAge: patient?.age || 0,
                patientPhone: patient?.phone || '',
                doctorId: createForm.doctorId,
                doctorName: doctor?.name || '',
                doctorTitle: doctor?.title || '',
                departmentId: createForm.departmentId,
                departmentName: department?.name || '',
                type: createForm.type,
                appointmentDate: createForm.date.format('YYYY-MM-DD'),
                appointmentTime: createForm.time,
                status: 0,
                createdAt: dayjs().format('YYYY-MM-DD HH:mm:ss'),
                remark: createForm.remark
            };

            appointments.value.unshift(newAppointment);
            message.success('挂号创建成功');
            createModalVisible.value = false;
            resetCreateForm();
        } else {
            message.error('创建挂号失败');
        }
    } catch (error) {
        console.error('创建挂号失败:', error);
        if (error.errorFields) {
            message.error('请检查表单填写是否正确');
        } else {
            message.error('创建挂号失败');
        }
    }
};

const viewDetails = (record) => {
    currentAppointment.value = record;
    detailModalVisible.value = true;
};

const startAppointment = async (record) => {
    try {
        const formData = new FormData();
        formData.append('appointment_id', record.id);
        formData.append('new_status', 1);

        const response = await updateAppointStatus(formData);
        if (response.data.code === 200) {
            const index = appointments.value.findIndex(a => a.id === record.id);
            if (index!== -1) {
                appointments.value[index].status = 1;
                message.success('挂号已开始');
            }
        } else {
            message.error('开始挂号操作失败');
        }
    } catch (error) {
        message.error('开始挂号操作失败' + error.message);
    }
};

const endAppointment = async (record) => {
    try {
        const formData = new FormData();
        formData.append('appointment_id', record.id);
        formData.append('new_status', 2);

        const response = await updateAppointStatus(formData);
        if (response.data.code === 200) {
            const index = appointments.value.findIndex(a => a.id === record.id);
            if (index!== -1) {
                appointments.value[index].status = 2;
                message.success('挂号已结束');
            }
        } else {
            message.error('结束挂号操作失败');
        }
    } catch (error) {
        message.error('结束挂号操作失败' + error.message);
    }
};

const cancelAppointment = async (id) => {
    try {
        const formData = new FormData();
        formData.append('appointment_id', id);
        formData.append('new_status', 2);

        const response = await updateAppointStatus(formData);
        if (response.data.code === 200) {
            const index = appointments.value.findIndex(a => a.id === id);
            if (index!== -1) {
                appointments.value[index].status = 2;
                message.success('挂号已取消');
            }
        } else {
            message.error('取消挂号操作失败');
        }
    } catch (error) {
        message.error('取消挂号操作失败' + error.message);
    }
};

const filterPatientOption = (input, option) => {
    return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const handlePatientSearch = (value) => {
    patientSearchText.value = value;
    filteredPatients.value = patients.value.filter(patient => {
        const searchText = patientSearchText.value.toLowerCase();
        return (
            patient.name.toLowerCase().includes(searchText) ||
            patient.id.toString().includes(searchText) ||
            patient.phone.includes(searchText)
        );
    });
};

const handleDoctorSelect = (value) => {
    createForm.time = null;
    // console.log(createForm)
    if (createForm.date) {
        fetchAvailableTimes();
    }
};

const exportToExcel = () => {
    message.success('导出Excel功能将在实际项目中实现');
    // 实际项目中这里会调用导出API或前端生成Excel文件
};

// 初始化
onMounted(() => {
    fetchAppointments();
});
</script>

<style scoped>
.registration-management {
    min-height: 100vh;
    background: #f0f2f5;
}

.page-header {
    background: #fff;
    padding: 16px 24px;
    margin-bottom: 1px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.content {
    padding: 24px;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.search-card,
.table-card {
    margin-bottom: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.patient-meta,
.doctor-meta {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.time-display { display: flex;
    flex-direction: column;
}

.time-display > div {
    line-height: 1.5;
}

.a-modal {
    border-radius: 8px;
}

.a-modal-content {
    border-radius: 8px;
}

.a-modal-header {
    border-bottom: 1px solid #f0f0f0;
    padding: 16px 24px;
}

.a-modal-title {
    font-size: 18px;
    font-weight: 600;
}

.a-modal-body {
    padding: 24px;
}

.a-modal-footer {
    border-top: 1px solid #f0f0f0;
    padding: 10px 16px;
    text-align: right;
}

.a-form-item-label {
    font-weight: 600;
}

.a-form-item {
    margin-bottom: 16px;
}

.a-table {
    border-radius: 8px;
    overflow: hidden;
}

.a-table-thead > tr > th {
    background: #fafafa;
    font-weight: 600;
}

.a-table-tbody > tr > td {
    border-bottom: 1px solid #f0f0f0;
}

.a-table-tbody > tr:hover > td {
    background: #fafafa;
}

.a-button {
    border-radius: 4px;
}

.a-button-primary {
    background: #1890ff;
    border-color: #1890ff;
}

.a-button-primary:hover {
    background: #40a9ff;
    border-color: #40a9ff;
}

.a-button-danger {
    background: #ff4d4f;
    border-color: #ff4d4f;
}

.a-button-danger:hover {
    background: #ff7875;
    border-color: #ff7875;
}

.a-tag {
    border-radius: 4px;
}

.a-breadcrumb {
    font-size: 14px;
}

.a-breadcrumb-item {
    color: #666;
}

.a-breadcrumb-item a {
    color: #1890ff;
}

.a-breadcrumb-item a:hover {
    color: #40a9ff;
}

.a-breadcrumb-separator {
    margin: 0 8px;
    color: #bfbfbf;
}
</style>