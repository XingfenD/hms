<template>
    <a-layout class="schedule-management">
        <!-- 顶部导航 -->
        <a-page-header title="医生排班管理" sub-title="管理系统医生排班信息" class="page-header" @back="() => router.push('/admin')">
            <template #backIcon>
                <arrow-left-outlined />
            </template>
            <template #breadcrumb>
                <a-breadcrumb>
                    <a-breadcrumb-item>
                        <router-link to="/admin">管理员面板</router-link>
                    </a-breadcrumb-item>
                    <a-breadcrumb-item>医生排班</a-breadcrumb-item>
                </a-breadcrumb>
            </template>
            <template #extra>
                <a-button type="primary" @click="showScheduleModal">
                    <template #icon><plus-outlined /></template>
                    新增排班
                </a-button>
                <!-- 添加智能排班按钮 -->
                <SmartScheduling @schedulingCompleted="fetchSchedules" />
            </template>
        </a-page-header>

        <a-layout>
            <!-- 侧边栏 -->
            <a-layout-sider width="200px" theme="light" :collapsible="false" class="site-layout-sider">
                <a-menu mode="inline" default-selected-keys="['calendar']" @select="handleViewChange"
                    class="sidebar-menu">
                    <a-menu-item key="calendar">
                        <template #icon><calendar-outlined /></template>
                        排班日历
                    </a-menu-item>
                    <a-menu-item key="list">
                        <template #icon><table-outlined /></template>
                        排班列表
                    </a-menu-item>
                </a-menu>
            </a-layout-sider>

            <a-layout-content class="content">
                <div class="container">
                    <!-- 视图容器 -->
                    <div v-if="currentView === 'calendar'" class="view-container">
                        <!-- 排班日历视图 -->
                        <a-card title="排班日历" class="schedule-card">
                            <div class="calendar-controls">
                                <a-select v-model:value="currentDepartment" style="width: 200px" placeholder="选择科室"
                                    @change="fetchDoctors" :loading="loadingDepartments">
                                    <a-select-option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </a-select-option>
                                </a-select>

                                <div class="calendar-nav">
                                    <a-button @click="prevWeek">
                                        <template #icon><left-outlined /></template>
                                    </a-button>
                                    <div class="current-week">
                                        {{ weekRange }}
                                    </div>
                                    <a-button @click="nextWeek">
                                        <template #icon><right-outlined /></template>
                                    </a-button>
                                </div>
                            </div>

                            <a-table :columns="scheduleColumns" :data-source="currentWeekSchedules" :pagination="false"
                                bordered size="middle" class="schedule-table" :loading="loadingSchedules">
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.key === 'doctor'">
                                        <a-tag color="blue">{{ record.doctorName }} </a-tag>
                                    </template>

                                    <template v-if="column.key === 'actions'">
                                        <a-space>
                                            <a-button size="small" @click="editSchedule(record)">
                                                <template #icon><edit-outlined /></template>
                                            </a-button>
                                            <a-popconfirm title="确定要删除这个排班吗？" ok-text="确定" cancel-text="取消"
                                                @confirm="deleteSchedule(record.id)">
                                                <a-button size="small" danger>
                                                    <template #icon><delete-outlined /></template>
                                                </a-button>
                                            </a-popconfirm>
                                        </a-space>
                                    </template>

                                    <template v-for="shift in ['morning', 'afternoon', 'night']" :key="shift">
                                        <template v-if="column.dataIndex === shift">
                                            <div v-if="record[shift].length" :class="`shift-slot ${shift}`">
                                                <div v-for="(schedule, index) in record[shift]"
                                                    :key="`${record.date}-${shift}-${index}`">
                                                    {{ schedule.doctorName }}
                                                    <a-button size="small"
                                                        @click="deleteShiftSchedule(record.date, shift, schedule.id)">
                                                        <template #icon><delete-outlined /></template>
                                                    </a-button>
                                                    <br>
                                                    <div class="shift-time">{{ getTimeRange(shift) }}</div>
                                                </div>
                                            </div>
                                            <a-button size="small" @click="showAddModal(record.date, shift)">
                                                <template #icon><plus-outlined /></template>
                                            </a-button>
                                        </template>
                                    </template>
                                </template>
                            </a-table>
                        </a-card>
                    </div>

                    <div v-if="currentView === 'list'" class="view-container">
                        <!-- 筛选表单 -->
                        <a-card class="filter-card">
                            <a-form layout="inline" :model="filterForm" @submit="handleFilter">
                                <a-form-item label="科室">
                                    <a-select v-model:value="filterForm.departmentId" placeholder="全部科室"
                                        :loading="loadingDepartments">
                                        <a-select-option value="">全部</a-select-option>
                                        <a-select-option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>

                                <a-form-item label="医生">
                                    <a-select v-model:value="filterForm.doctorId" placeholder="全部医生"
                                        :loading="loadingDoctors">
                                        <a-select-option value="">全部</a-select-option>
                                        <a-select-option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                            {{ doctor.name }} ({{ doctor.title }})
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>

                                <a-form-item label="班次">
                                    <a-select v-model:value="filterForm.shift" placeholder="全部班次">
                                        <a-select-option value="">全部</a-select-option>
                                        <a-select-option value="morning">上午班</a-select-option>
                                        <a-select-option value="afternoon">下午班</a-select-option>
                                        <a-select-option value="night">晚班</a-select-option>
                                    </a-select>
                                </a-form-item>

                                <a-form-item label="搜索">
                                    <a-input v-model:value="filterForm.searchKeyword" placeholder="输入关键词搜索" />
                                </a-form-item>
                            </a-form>
                        </a-card>

                        <!-- 排班列表视图 -->
                        <a-card title="排班列表" class="list-card">
                            <a-table :columns="listColumns" :data-source="filteredSchedules"
                                :pagination="{ pageSize: 10 }" row-key="id" :loading="loadingSchedules">
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.key === 'actions'">
                                        <a-space>
                                            <a-button size="small" @click="editSchedule(record)">
                                                <template #icon><edit-outlined /></template>
                                            </a-button>
                                            <a-popconfirm title="确定要删除这个排班吗？" ok-text="确定" cancel-text="取消"
                                                @confirm="deleteSchedule(record.id)">
                                                <a-button size="small" danger>
                                                    <template #icon><delete-outlined /></template>
                                                </a-button>
                                            </a-popconfirm>
                                        </a-space>
                                    </template>
                                </template>
                            </a-table>
                        </a-card>
                    </div>
                </div>
            </a-layout-content>
        </a-layout>

        <!-- 添加/编辑排班模态框 -->
        <a-modal v-model:visible="scheduleModalVisible" :title="`${editMode ? '编辑' : '新增'}排班`" width="600px"
            :mask-closable="false" :destroy-on-close="true" @ok="handleScheduleSubmit" @cancel="resetScheduleForm"
            :confirm-loading="submittingSchedule">
            <a-form ref="scheduleFormRef" :model="scheduleForm" :rules="scheduleRules" layout="vertical">
                <a-row :gutter="16">
                    <a-col :span="12">
                        <a-form-item label="选择科室" name="departmentId">
                            <a-select v-model:value="scheduleForm.departmentId" placeholder="请选择科室"
                                @change="fetchDoctorsForForm" :loading="loadingDepartments">
                                <a-select-option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="选择医生" name="doctorId">
                            <a-select v-model:value="scheduleForm.doctorId" placeholder="请选择医生"
                                :disabled="!scheduleForm.departmentId" :loading="loadingDoctorsForForm">
                                <a-select-option v-for="doctor in doctorsForForm" :key="doctor.doctor_id" :value="doctor.doctor_id">
                                    {{ doctor.name }} ({{ doctor.title }})
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :span="12">
                        <a-form-item label="排班日期" name="date">
                            <a-date-picker v-model:value="scheduleForm.date" style="width: 100%"
                                :disabled-date="disabledDate" />
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="班次" name="shift">
                            <a-select v-model:value="scheduleForm.shift" placeholder="请选择班次">
                                <a-select-option value="morning">
                                    上午班 (08:30-12:00)
                                </a-select-option>
                                <a-select-option value="afternoon">
                                    下午班 (14:00-17:30)
                                </a-select-option>
                                <a-select-option value="night">
                                    晚班 (18:00-次日07:00)
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-alert v-if="scheduleForm.shift === 'night'" message="晚班注意事项" description="晚班将自动跨天排班，从当天18:00到次日07:00"
                    type="info" show-icon style="margin-bottom: 16px;" />
            </a-form>
        </a-modal>
    </a-layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
    ArrowLeftOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    LeftOutlined,
    RightOutlined,
    CalendarOutlined,
    TableOutlined
} from '@ant-design/icons-vue';
import { message, Modal } from 'ant-design-vue';
import dayjs from 'dayjs';
import weekday from 'dayjs/plugin/weekday';
import weekOfYear from 'dayjs/plugin/weekOfYear';
import {
    getDepartments,
    getDoctorsByDepartment,
    getSchedules,
    addSchedule,
    updateSchedule,
    deleteSchedule as apiDeleteSchedule
} from '@/api/api';
// 引入智能排班组件
import SmartScheduling from '@/components/DoctorSmartScheduling.vue';

dayjs.extend(weekday);
dayjs.extend(weekOfYear);

const router = useRouter();

// 侧边栏视图切换
const currentView = ref('calendar');
const handleViewChange = (e) => {
    currentView.value = e.key;
};

// 数据状态
const departments = ref([]);
const doctors = ref([]);
const doctorsForForm = ref([]);
const schedules = ref([]);
const currentDepartment = ref(null);
const currentWeekStart = ref(dayjs().startOf('week'));

// 加载状态
const loadingDepartments = ref(false);
const loadingDoctors = ref(false);
const loadingDoctorsForForm = ref(false);
const loadingSchedules = ref(false);
const submittingSchedule = ref(false);

// 筛选表单
const filterForm = reactive({
    departmentId: '',
    doctorId: '',
    shift: '',
    searchKeyword: ''
});

// 模态框状态
const scheduleModalVisible = ref(false);
const editMode = ref(false);
const scheduleFormRef = ref();
const scheduleForm = reactive({
    id: null,
    departmentId: null,
    doctorId: null,
    date: null,
    shift: 'morning'
});

const scheduleRules = {
    departmentId: [{ required: true, message: '请选择科室' }],
    doctorId: [{ required: true, message: '请选择医生' }],
    date: [{ required: true, message: '请选择日期' }],
    shift: [{ required: true, message: '请选择班次' }]
};

// 表格列定义
const scheduleColumns = computed(() => {
    const baseColumns = [
        {
            title: '日期',
            dataIndex: 'date',
            key: 'date',
            width: 120,
            customRender: ({ text }) => {
                const date = dayjs(text);
                return `${date.format('MM-DD')} ${getWeekday(date)}`;
            }
        }
    ];

    return [
        ...baseColumns,
        {
            title: '上午班',
            dataIndex: 'morning',
            key: 'morning',
            width: 180
        },
        {
            title: '下午班',
            dataIndex: 'afternoon',
            key: 'afternoon',
            width: 180
        },
        {
            title: '晚班',
            dataIndex: 'night',
            key: 'night',
            width: 180
        }
    ];
});

const listColumns = [
    {
        title: '医生姓名',
        dataIndex: 'doctorName',
        key: 'doctorName',
        width: 120
    },
    {
        title: '科室',
        dataIndex: 'department',
        key: 'department',
        width: 100
    },
    {
        title: '排班日期',
        dataIndex: 'date',
        key: 'date',
        width: 120,
        customRender: ({ text }) => dayjs(text).format('YYYY-MM-DD')
    },
    {
        title: '班次',
        dataIndex: 'shift',
        key: 'shift',
        width: 120
    },
    {
        title: '时间段',
        dataIndex: 'timeRange',
        key: 'timeRange',
        width: 180
    },
    {
        title: '操作',
        key: 'actions',
        width: 120
    }
];

// 计算属性
const weekRange = computed(() => {
    const start = currentWeekStart.value.format('MM/DD');
    const end = currentWeekStart.value.add(6, 'day').format('MM/DD');
    return `${start} - ${end}`;
});

const currentWeekDates = computed(() => {
    return Array.from({ length: 7 }, (_, i) =>
        currentWeekStart.value.add(i, 'day').format('YYYY-MM-DD')
    );
});

const currentWeekSchedules = computed(() => {
    const filteredSchedulesByDepartment = schedules.value.filter(s => s.departmentId === currentDepartment.value);
    // console.log(currentDepartment.value);
    // console.log(filteredSchedulesByDepartment);
    // console.log(schedules.value);
    return currentWeekDates.value.map(date => {
        const dateSchedules = filteredSchedulesByDepartment.filter(s => s.date === date);
        return {
            date,
            morning: dateSchedules.filter(s => s.shift === 'morning'),
            afternoon: dateSchedules.filter(s => s.shift === 'afternoon'),
            night: dateSchedules.filter(s => s.shift === 'night'),
            doctorName: dateSchedules[0]?.doctorName,
            departmentId: dateSchedules[0]?.departmentId
        };
    });
});

const filteredSchedules = computed(() => {
    let filtered = schedules.value;
    if (filterForm.departmentId !== '') {
        filtered = filtered.filter(s => s.departmentId === filterForm.departmentId);
    }
    if (filterForm.doctorId !== '') {
        filtered = filtered.filter(s => s.doctorId === filterForm.doctorId);
    }
    if (filterForm.shift !== '') {
        filtered = filtered.filter(s => s.shift === filterForm.shift);
    }
    if (filterForm.searchKeyword) {
        const keyword = filterForm.searchKeyword.toLowerCase();
        filtered = filtered.filter(schedule => {
            return (
                schedule.doctorName.toLowerCase().includes(keyword) ||
                schedule.department.toLowerCase().includes(keyword) ||
                schedule.shift.toLowerCase().includes(keyword) ||
                dayjs(schedule.date).format('YYYY-MM-DD').includes(keyword)
            );
        });
    }
    return filtered.map(schedule => ({
        ...schedule,
        timeRange: getTimeRange(schedule)
    }));
});

// 辅助方法
const getWeekday = (date) => {
    const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
    return weekdays[dayjs(date).day()];
};

const getTimeRange = (schedule) => {
    switch (schedule.shift) {
        case 'morning': return '08:30-12:00';
        case 'afternoon': return '14:00-17:30';
        case 'night': return '18:00-次日07:00';
        default: return '';
    }
};

const getDepartmentId = (schedule) => {
    switch (schedule.Department) {
        case '内科': return 1;
        case '外科': return 2;
        case '儿科': return 3;
        case '妇产科': return 4;
        case '眼科': return 5;
        default: return 1;
    }
}

const disabledDate = (current) => {
    return current && current < dayjs().startOf('day');
};

const getshift = (start_time, end_time) => {
    const morningStart = '08:30:00';
    const morningEnd = '12:00:00';
    const afternoonStart = '14:00:00';
    const afternoonEnd = '17:30:00';
    const nightStart = '18:00:00';
    const nightEnd = '07:00:00';

    if (start_time === morningStart && end_time === morningEnd) {
        return 'morning';
    } else if (start_time === afternoonStart && end_time === afternoonEnd) {
        return 'afternoon';
    } else if (start_time === nightStart && end_time === nightEnd) {
        return 'night';
    }
    return null;
};

// API 调用方法
const fetchDepartments = async () => {
    try {
        loadingDepartments.value = true;
        const response = await getDepartments();
        departments.value = response.data.map(dept => ({
            id: dept.dep_id,
            name: dept.dep_name
        }));
        if (departments.value.length > 0) {
            currentDepartment.value = departments.value[0].id;
            scheduleForm.departmentId = currentDepartment.value;
            await fetchDoctors();
        }
    } catch (error) {
        message.error('获取科室列表失败: ' + (error.response?.data?.message || error.message));
    } finally {
        loadingDepartments.value = false;
    }
};

const fetchDoctors = async () => {
    if (!currentDepartment.value) return;

    try {
        loadingDoctors.value = true;
        const response = await getDoctorsByDepartment(currentDepartment.value);
        console.log(response.data)
        doctors.value = response.data.data.map(doctor => ({
            id: doctor.DoctorID,
            name: doctor.FullName,
            title: doctor.title,
            departmentId: currentDepartment.value,
            doctor_id: doctor.DoctorOwnID
        }));
        await fetchSchedules();
    } catch (error) {
        message.error('获取医生列表失败: ' + (error.response?.data?.message || error.message));
    } finally {
        loadingDoctors.value = false;
    }
};

const fetchDoctorsForForm = async () => {
    if (!scheduleForm.departmentId) return;

    try {
        loadingDoctorsForForm.value = true;
        const response = await getDoctorsByDepartment(scheduleForm.departmentId);
        // console.log(response.data)
        doctorsForForm.value = response.data.data.map(doctor => ({
            id: doctor.DoctorID,
            name: doctor.FullName,
            title: doctor.title,
            departmentId: scheduleForm.departmentId,
            doctor_id: doctor.DoctorOwnID
        }));
    } catch (error) {
        message.error('获取医生列表失败: ' + (error.response?.data?.message || error.message));
    } finally {
        loadingDoctorsForForm.value = false;
    }
};

const fetchSchedules = async () => {
    try {
        loadingSchedules.value = true;
        const params = {
            start_date: currentWeekStart.value.format('YYYY-MM-DD'),
            end_date: currentWeekStart.value.add(6, 'day').format('YYYY-MM-DD')
        };

        console.log(params.start_date, params.end_date)
        const response = await getSchedules(params);
        console.log(response.data);
        // 检查响应数据是否存在且包含 data 字段
        if (response.data && response.data.data && response.data.data.length > 0) {
            schedules.value = response.data.data.map(schedule => ({
                id: schedule.schedule_id,
                doctorId: schedule.doctor_id,
                doctorName: schedule.DoctorName,
                departmentId: getDepartmentId(schedule),
                department: schedule.Department,
                date: schedule.ScheduleDate,
                shift: getshift(schedule.StartTime, schedule.EndTime) // 使用 getshift 函数获取班次
            }));
            console.log(schedules.value);
        } else {
            // 若数据为空，清空本地排班信息
            schedules.value = [];
            message.info('该时间段内没有排班信息。');
        }

    } catch (error) {
        message.error('获取排班信息失败: ' + (error.response?.data?.message || error.message));
    } finally {
        loadingSchedules.value = false;
    }
};

const prevWeek = () => {
    currentWeekStart.value = currentWeekStart.value.subtract(7, 'day');
    fetchSchedules();
};

const nextWeek = () => {
    currentWeekStart.value = currentWeekStart.value.add(7, 'day');
    fetchSchedules();
};

const showScheduleModal = () => {
    editMode.value = false;
    scheduleForm.departmentId = currentDepartment.value;
    scheduleModalVisible.value = true;
};

const showAddModal = (date, shift) => {
    editMode.value = false;
    scheduleForm.date = dayjs(date);
    scheduleForm.shift = shift;
    scheduleForm.departmentId = currentDepartment.value;
    scheduleModalVisible.value = true;
};

const editSchedule = (schedule) => {
    editMode.value = true;
    Object.assign(scheduleForm, {
        id: schedule.id,
        departmentId: schedule.departmentId,
        doctorId: schedule.doctorId,
        date: dayjs(schedule.date),
        shift: schedule.shift
    });
    scheduleModalVisible.value = true;
};

const resetScheduleForm = () => {
    scheduleFormRef.value?.resetFields();
    Object.assign(scheduleForm, {
        id: null,
        departmentId: currentDepartment.value,
        doctorId: null,
        date: null,
        shift: 'morning'
    });
};

const handleScheduleSubmit = async () => {
    try {
        await scheduleFormRef.value.validate();
        submittingSchedule.value = true;

        const formData = {
            doctor_id: scheduleForm.doctorId,
            // department_id: scheduleForm.departmentId,
            schedule_date: scheduleForm.date.format('YYYY-MM-DD'),
            shift: scheduleForm.shift
        };

        let response;
        if (editMode.value) {
            formData.schedule_id = scheduleForm.id;
            response = await updateSchedule(formData);
            message.success('排班更新成功');
        } else {
            response = await addSchedule(formData);
            // console.log(formData);
            console.log(response.data);
            if (response.data.code == 200) {
                message.success('排班添加成功');
            } else {
                message.error(response.data.message);
            }
        }

        // 更新本地数据
        await fetchSchedules();
        scheduleModalVisible.value = false;
        resetScheduleForm();
    } catch (error) {
        console.error('排班操作失败:', error);
        message.error(error.errorFields ? '请检查表单填写是否正确' :
            (error.response?.data?.message || '排班操作失败'));
    } finally {
        submittingSchedule.value = false;
    }
};

const deleteSchedule = async (id) => {
    try {
        const response = await apiDeleteSchedule(id);
        console.log(response.data);
        message.success('排班删除成功');

        await fetchSchedules();
    } catch (error) {
        message.error('删除排班失败: ' + (error.response?.data?.message || error.message));
    }
};

const deleteShiftSchedule = async (date, shift, id) => {
    try {
        const response = await apiDeleteSchedule(id);
        console.log(response);
        message.success('排班删除成功');
        await fetchSchedules();
    } catch (error) {
        message.error('删除排班失败: ' + (error.response?.data?.message || error.message));
    }
};

const handleFilter = () => {
    // 筛选逻辑已在计算属性中处理
};

// 监听 scheduleForm.departmentId 的变化
watch(() => scheduleForm.departmentId, async (newValue) => {
    if (newValue) {
        await fetchDoctorsForForm();
    }
});

onMounted(() => {
    fetchDepartments();
    if (scheduleForm.departmentId) {
        fetchDoctorsForForm();
    }
});
</script>

<style scoped>
.schedule-management {
    min-height: 100vh;
    background: #f0f2f5;
}

.site-layout-sider {
    background: #f8f9fa;
    border-right: 1px solid #e8e8e8;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: width 0.3s ease;
}

.sider-logo {
    height: 64px;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.sider-logo i {
    margin-right: 8px;
    font-size: 24px;
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

.schedule-card,
.list-card,
.filter-card {
    margin-bottom: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.calendar-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 16px;
}

.current-week {
    font-weight: 500;
    min-width: 180px;
    text-align: center;
}

.schedule-table {
    margin-top: 16px;
}

.shift-slot {
    padding: 8px;
    border-radius: 4px;
    text-align: center;
}

.shift-slot.morning {
    background-color: #e6f7ff;
    border: 1px solid #91d5ff;
}

.shift-slot.afternoon {
    background-color: #f6ffed;
    border: 1px solid #b7eb8f;
}

.shift-slot.night {
    background-color: #fff2e8;
    border: 1px solid #ffbb96;
}

.shift-time {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.sidebar-menu {
    border-right: none;
}

.sidebar-menu .ant-menu-item {
    padding-left: 24px;
    font-size: 18px;
    color: #333;
    transition: all 0.3s;
    position: relative;
}

.sidebar-menu .ant-menu-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background-color: #1890ff;
    opacity: 0;
    transition: opacity 0.3s;
}

.sidebar-menu .ant-menu-item:hover {
    background-color: #f0f2f5;
    color: #1890ff;
}

.sidebar-menu .ant-menu-item:hover::before {
    opacity: 1;
}

.sidebar-menu .ant-menu-item-selected {
    background-color: #e6f7ff;
    color: #1890ff;
    border-right: none;
}

.sidebar-menu .ant-menu-item-selected::before {
    opacity: 1;
}

.sidebar-menu .ant-menu-item-icon {
    font-size: 18px;
    margin-right: 12px;
    color: #666;
    transition: color 0.3s;
}

.sidebar-menu .ant-menu-item:hover .ant-menu-item-icon,
.sidebar-menu .ant-menu-item-selected .ant-menu-item-icon {
    color: #1890ff;
}

@media (max-width: 768px) {
    .content {
        padding: 12px;
    }

    .calendar-controls {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
}
</style>    