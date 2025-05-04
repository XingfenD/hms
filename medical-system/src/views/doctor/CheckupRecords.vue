<template>
  <div class="checkup-records-container">
    <a-card title="检查记录" :bordered="false">
      <!-- 筛选区域 -->
      <div class="filter-container">
        <a-form layout="inline" :model="filterForm">
          <a-form-item label="检查状态">
            <!-- 这里根据实际 status_code 含义修改选项 -->
            <a-select
              v-model:value="filterForm.status"
              style="width: 120px"
              placeholder="全部状态"
              allow-clear
            >
              <a-select-option value="0">未检查</a-select-option>
              <a-select-option value="1">已付款</a-select-option>
              <a-select-option value="2">已付款已检查</a-select-option>
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

      <!-- 检查记录列表 -->
      <a-table
        :columns="columns"
        :data-source="filteredCheckupRecords"
        :row-key="(record) => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'patientName'">
            {{ record.patient.name }}
          </template>
          <template v-if="column.key === 'checkupItems'">
            {{ record.checkupItems.map(item => item.name).join(', ') }}
          </template>
          <template v-if="column.key === 'diagnosis'">
            {{ record.diagnosis }}
          </template>
          <template v-if="column.key === 'checkupDate'">
            {{ record.checkupDate }}
          </template>
          <template v-if="column.key === 'action'">
            <a-button type="link" @click="viewDetails(record)">查看详情</a-button>
          </template>
        </template>
      </a-table>
    </a-card>
    <div v-if="filteredCheckupRecords.length === 0 && (filterForm.patientName || filterForm.status)" class="text-red-500">
      未找到符合筛选条件的记录，请尝试其他条件。
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { SearchOutlined, RedoOutlined } from '@ant-design/icons-vue';
import {
  getExamRecord,
  getCheckupResult,
  getAppoint,
  getLoginInfo,
  get_single_userinfo_data,
  getCheckupRecord,
  getCheckup,
} from '@/api/api'; // 假设这些 API 函数在 api.js 文件中

const checkupRecords = ref([]);
const columns = [
  {
    title: '患者姓名',
    key: 'patientName',
    width: '20%',
  },
  {
    title: '检查项目',
    key: 'checkupItems',
    width: '30%',
  },
  {
    title: '诊断结果',
    key: 'diagnosis',
    width: '30%',
  },
  {
    title: '检查日期',
    key: 'checkupDate',
    width: '15%',
  },
  {
    title: '操作',
    key: 'action',
    width: '5%',
  },
];
const pagination = ref({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  pageSizeOptions: ['10', '20', '50'],
  showTotal: total => `共 ${total} 条`
});
const filterForm = ref({
  status: undefined,
  patientName: '',
});
const loading = ref(false);

const handleTableChange = (paginationInfo) => {
  pagination.value = {
    ...pagination.value,
    ...paginationInfo,
  };
  handleSearch();
};

const viewDetails = (record) => {
  message.info(`查看 ${record.patient.name} 的检查记录详情`);
  // 这里可以添加跳转到详情页的逻辑
};

const fetchData = async () => {
  try {
    loading.value = true;
    // 获取医生 ID
    const loginInfoRes = await getLoginInfo();
    const doctorId = loginInfoRes.data.data.doctor_id;

    // 获取挂号信息
    const appointRes = await getAppoint({ doctor_id: doctorId });
    const appointData = appointRes.data.data;

    // 获取所有检查项目信息
    const checkupRes = await getCheckup({});
    const checkupList = checkupRes.data.data;

    const newCheckupRecords = [];
    for (const appoint of appointData) {
      const appointId = appoint.app_if.AppointmentID;
      // 根据挂号 ID 获取检查记录
      const checkupRecordRes = await getCheckupRecord(appointId);
      const checkupRecordsData = checkupRecordRes.data.data;

      // 检查是否有检查记录
      if (checkupRecordRes.data.code !== 200 || checkupRecordsData.length === 0) {
        continue; // 没有检查记录，跳过该挂号信息
      }

      for (const checkupRecord of checkupRecordsData) {
        const resultRes = await getCheckupResult(checkupRecord.exam_id);
        console.log(resultRes.data, checkupRecord.exam_id)
        let diagnosis = null;
        if (resultRes.data.code === 200) {
          console.log(resultRes.data)
          diagnosis = resultRes.data.data.exam_result;
        }
        const patient = appoint.pat_if;
        // 获取患者详细信息
        const userInfoRes = await get_single_userinfo_data(patient.PatientId);
        const userInfo = userInfoRes.data.data[0];

        // 根据 exam_def_id 查找对应的检查项目名称
        const checkupItem = checkupList.find(
          (item) => item.exam_def_id === checkupRecord.exam_def_id
        );
        const checkupItemName = checkupItem
          ? checkupItem.exam_name
          : `未知检查项目${checkupRecord.exam_def_id}`;

        newCheckupRecords.push({
          id: checkupRecord.exam_id,
          patient: {
            name: userInfo.Username,
            gender: patient.PatientGender === 0 ? 'female' : 'male',
            age: patient.PatientAge,
          },
          checkupItems: [
            { name: checkupItemName },
          ],
          diagnosis,
          checkupDate: checkupRecord.oper_time,
          status_code: checkupRecord.status_code // 保存状态码
        });
      }
    }
    checkupRecords.value = newCheckupRecords;
    pagination.value.total = newCheckupRecords.length;
  } catch (error) {
    console.error('数据获取失败:', error);
    message.error('获取检查记录列表失败');
  } finally {
    loading.value = false;
  }
};

const filteredCheckupRecords = computed(() => {
  let filtered = checkupRecords.value;

  if (filterForm.value.patientName) {
    filtered = filtered.filter(record =>
      record.patient.name.includes(filterForm.value.patientName)
    );
  }

  if (filterForm.value.status) {
    filtered = filtered.filter(record => record.status_code === parseInt(filterForm.value.status));
  }

  return filtered;
});

const handleSearch = () => {
  pagination.value.current = 1;
  // 这里可根据实际情况，调用接口并传入筛选条件
  // 目前只是简单重新计算筛选结果
  // 实际使用时可能需要修改 fetchData 函数以支持筛选条件
  // fetchData(filterForm.value);
};

const resetSearch = () => {
  filterForm.value = {
    status: undefined,
    patientName: '',
  };
  handleSearch();
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.checkup-records-container {
  padding: 24px;
}

.filter-container {
  margin-bottom: 24px;
}
</style>    