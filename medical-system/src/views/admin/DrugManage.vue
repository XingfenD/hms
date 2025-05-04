<template>
  <a-layout class="drug-management">
    <!-- 顶部导航 -->
    <a-page-header
      title="药品管理"
      sub-title="管理系统药品库存与发药流程"
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
          <a-breadcrumb-item>药品管理</a-breadcrumb-item>
        </a-breadcrumb>
      </template>
      <template #extra>
        <a-button type="primary" @click="showAddDrugModal" v-if="activeTab === 'drugs'">
          <template #icon><plus-outlined /></template>
          新增药品
        </a-button>
      </template>
    </a-page-header>

    <a-layout>
      <!-- 侧边栏 -->
      <a-layout-sider width="200" style="background: #fff">
        <a-menu
          v-model:selectedKeys="selectedKeys"
          mode="inline"
          style="height: 100%"
          @select="handleMenuSelect"
        >
          <a-menu-item key="drugs">
            <template #icon>
              <medicine-box-outlined />
            </template>
            药品库存
          </a-menu-item>
          <a-menu-item key="prescriptions">
            <template #icon>
              <file-text-outlined />
            </template>
            待发药处方
          </a-menu-item>
        </a-menu>
      </a-layout-sider>

      <!-- 主内容区 -->
      <a-layout-content class="content">
        <div class="container">
          <!-- 药品库存卡片 -->
          <a-card 
            title="药品库存" 
            class="drug-card" 
            v-if="activeTab === 'drugs'"
          >
            <template #extra>
              <a-input-search
                v-model:value="drugSearchText"
                placeholder="搜索药品..."
                style="width: 200px"
                @search="handleDrugSearch"
              />
            </template>

            <a-table
              :columns="drugColumns"
              :data-source="filteredDrugs"
              :pagination="{ pageSize: 10 }"
              row-key="id"
              bordered
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'stockStatus'">
                  <a-tag :color="getStockStatusColor(record.stock)">
                    {{ getStockStatusText(record.stock) }}
                  </a-tag>
                </template>
                
                <template v-if="column.key === 'actions'">
                  <a-space>
                    <!-- <a-button size="small" @click="editDrug(record)">
                      <template #icon><edit-outlined /></template>
                    </a-button> -->
                    <a-popconfirm
                      title="确定要删除这个药品吗？"
                      ok-text="确定"
                      cancel-text="取消"
                      @confirm="deleteDrug(record.id)"
                    >
                      <a-button size="small" danger>
                        <template #icon><delete-outlined /></template>
                      </a-button>
                    </a-popconfirm>
                    <a-button size="small" @click="showStockModal(record)">
                      <template #icon><plus-circle-outlined /></template>
                      补货
                    </a-button>
                  </a-space>
                </template>
              </template>
            </a-table>
          </a-card>

          <!-- 待发药处方卡片 -->
          <a-card 
            title="待发药处方" 
            class="prescription-card" 
            v-if="activeTab === 'prescriptions'"
          >
            <template #extra>
              <a-space>
                <a-input-search
                  v-model:value="prescriptionSearchText"
                  placeholder="搜索患者/医生..."
                  style="width: 200px"
                  @search="handlePrescriptionSearch"
                />
                <a-button type="primary" @click="batchDispense" :disabled="selectedPrescriptions.length === 0">
                  <template #icon><medicine-box-outlined /></template>
                  批量发药
                </a-button>
              </a-space>
            </template>

            <a-table
              :columns="prescriptionColumns"
              :data-source="filteredPrescriptions"
              :pagination="{ pageSize: 10 }"
              :row-selection="rowSelection"
              row-key="id"
              bordered
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'patientInfo'">
                  <router-link :to="`/patient/detail/${record.patientId}`">
                    {{ record.patientName }} (ID: {{ record.patientId }})
                  </router-link>
                </template>
                
                <template v-if="column.key === 'doctorInfo'">
                  <router-link :to="`/doctor/detail/${record.doctorId}`">
                    {{ record.doctorName }} (ID: {{ record.doctorId }})
                  </router-link>
                </template>
                
                <template v-if="column.key === 'drugInfo'">
                  <router-link :to="`/drug/detail/${record.drugId}`">
                    {{ record.drugName }} (ID: {{ record.drugId }})
                  </router-link>
                  <div class="drug-meta">
                    <span>库存: {{ record.drugStock }} | 单价: ¥{{ record.price }}</span>
                  </div>
                </template>

                
                
                <template v-if="column.key === 'totalPrice'">
                  ¥{{ (parseFloat(record.payed_amount) * parseFloat(record.price)).toFixed(2) }}
                </template>
                
                <template v-if="column.key === 'status'">
                  <a-tag :color="getStatusColor(record.status_code)">
                    {{ getStatusText(record.status_code) }}
                  </a-tag>
                </template>
                
                <template v-if="column.key === 'actions'">
                  <a-space>
                    <a-button 
                      size="small" 
                      type="primary" 
                      @click="dispenseMedication(record)"
                      :disabled="record.status_code!== 1 || record.drugStock < parseFloat(record.payed_amount)"
                    >
                      <template #icon><medicine-box-outlined /></template>
                      发药
                    </a-button>
                    <a-tooltip v-if="record.drugStock < parseFloat(record.payed_amount)" title="库存不足">
                      <exclamation-circle-outlined style="color: #ff4d4f" />
                    </a-tooltip>
                  </a-space>
                </template>
              </template>
            </a-table>
          </a-card>
        </div>
      </a-layout-content>
    </a-layout>

    <!-- 新增药品模态框 -->
    <a-modal
      v-model:visible="addDrugModalVisible"
      title="新增药品"
      width="600px"
      :mask-closable="false"
      :destroy-on-close="true"
      @ok="handleAddDrug"
      @cancel="resetAddDrugForm"
    >
      <a-form
        ref="addDrugFormRef"
        :model="addDrugForm"
        :rules="addDrugRules"
        layout="vertical"
      >
        <a-row :gutter="24">
          <a-col :span="12">
            <a-form-item label="药品名称" name="name">
              <a-input
                v-model:value="addDrugForm.name"
                placeholder="请输入药品名称"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="药品规格" name="specification">
              <a-input
                v-model:value="addDrugForm.specification"
                placeholder="如: 10mg*24片"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="24">
          <a-col :span="12">
            <a-form-item label="库存数量" name="stock">
              <a-input-number
                v-model:value="addDrugForm.stock"
                :min="0"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="单价(元)" name="price">
              <a-input-number
                v-model:value="addDrugForm.price"
                :min="0"
                :precision="2"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="24">
          <a-col :span="12">
            <a-form-item label="生产厂家" name="manufacturer">
              <a-input
                v-model:value="addDrugForm.manufacturer"
                placeholder="请输入生产厂家"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="药品类别" name="category">
              <a-select
                v-model:value="addDrugForm.category"
                placeholder="请选择药品类别"
              >
                <a-select-option value="西药">西药</a-select-option>
                <a-select-option value="中药">中药</a-select-option>
                <a-select-option value="中成药">中成药</a-select-option>
                <a-select-option value="医疗器械">医疗器械</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="药品说明" name="description">
          <a-textarea
            v-model:value="addDrugForm.description"
            placeholder="请输入药品说明"
            :rows="3"
          />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 编辑药品模态框 -->
    <a-modal
      v-model:visible="editDrugModalVisible"
      :title="`编辑药品 (ID: ${currentDrug?.id || ''})`"
      width="600px"
      :mask-closable="false"
      :destroy-on-close="true"
      @ok="handleEditDrug"
      @cancel="resetEditDrugForm"
    >
      <a-form
        ref="editDrugFormRef"
        :model="editDrugForm"
        :rules="editDrugRules"
        layout="vertical"
      >
        <!-- 表单内容与新增药品相同 -->
      </a-form>
    </a-modal>

    <!-- 药品补货模态框 -->
    <a-modal
      v-model:visible="stockModalVisible"
      :title="`药品补货 (${currentDrug?.name || ''})`"
      width="500px"
      :mask-closable="false"
      :destroy-on-close="true"
      @ok="handleStockUpdate"
      @cancel="resetStockForm"
    >
      <a-form
        ref="stockFormRef"
        :model="stockForm"
        :rules="stockRules"
        layout="vertical"
      >
        <a-form-item label="当前库存">
          <a-input-number
            :value="currentDrug?.stock || 0"
            disabled
            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="补货数量" name="quantity">
          <a-input-number
            v-model:value="stockForm.quantity"
            :min="1"
            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="补货原因" name="reason">
          <a-textarea
            v-model:value="stockForm.reason"
            placeholder="请输入补货原因"
            :rows="3"
          />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 发药确认模态框 -->
    <a-modal
      v-model:visible="dispenseModalVisible"
      :title="`发药确认 (处方ID: ${currentPrescription?.id || ''})`"
      width="600px"
      :mask-closable="false"
      @ok="confirmDispense"
      @cancel="cancelDispense"
    >
      <a-descriptions bordered :column="1" v-if="currentPrescription">
        <a-descriptions-item label="患者信息">
          {{ currentPrescription.patientName }} (ID: {{ currentPrescription.patientId }})
        </a-descriptions-item>
        <a-descriptions-item label="医生信息">
          {{ currentPrescription.doctorName }} (ID: {{ currentPrescription.doctorId }})
        </a-descriptions-item>
        <a-descriptions-item label="药品信息">
          {{ currentPrescription.drugName }} (ID: {{ currentPrescription.drugId }})
          <div>数量: {{ currentPrescription.payed_amount }}</div>
          <div>单价: ¥{{ currentPrescription.price }}</div>
          <div>总价: ¥{{ (parseFloat(currentPrescription.payed_amount) * parseFloat(currentPrescription.price)).toFixed(2) }}</div>
        </a-descriptions-item>
        <a-descriptions-item label="当前库存">
          {{ currentPrescription.drugStock }}
          <a-tag v-if="currentPrescription.drugStock < parseFloat(currentPrescription.payed_amount)" color="red">
            库存不足
          </a-tag>
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
  EditOutlined,
  DeleteOutlined,
  PlusCircleOutlined,
  MedicineBoxOutlined,
  ExclamationCircleOutlined,
  SearchOutlined,
  FileTextOutlined
} from '@ant-design/icons-vue';
import { message, Modal } from 'ant-design-vue';
import { getDrugs, addDrugs, addStore, deleteDrugs, updateStore, getPrescriptions, dispensePres } from '@/api/api';

const router = useRouter();

// 药品数据
const drugs = ref([]);
const drugSearchText = ref('');

// 处方数据
const prescriptions = ref([]);
const prescriptionSearchText = ref('');
const selectedPrescriptions = ref([]);

// 当前操作的药品/处方
const currentDrug = ref(null);
const currentPrescription = ref(null);

// 侧边栏状态
const selectedKeys = ref(['drugs']);
const activeTab = ref('drugs');

// 模态框状态
const addDrugModalVisible = ref(false);
const editDrugModalVisible = ref(false);
const stockModalVisible = ref(false);
const dispenseModalVisible = ref(false);

// 表单数据
const addDrugForm = reactive({
  name: '',
  specification: '',
  stock: 0,
  price: 0,
  manufacturer: '',
  category: '',
  description: ''
});

const editDrugForm = reactive({
  id: null,
  name: '',
  specification: '',
  stock: 0,
  price: 0,
  manufacturer: '',
  category: '',
  description: ''
});

const stockForm = reactive({
  quantity: 1,
  reason: ''
});

// 表单验证规则
const addDrugRules = {
  name: [{ required: true, message: '请输入药品名称' }],
  stock: [{ required: true, message: '请输入库存数量' }],
  price: [{ required: true, message: '请输入药品单价' }]
};

const editDrugRules = { ...addDrugRules };
const stockRules = {
  quantity: [{ required: true, message: '请输入补货数量' }],
  reason: [{ required: true, message: '请输入补货原因' }]
};

// 表格列定义
const drugColumns = [
  {
    title: '药品ID',
    dataIndex: 'id',
    key: 'id',
    width: 100,
    sorter: (a, b) => a.id - b.id
  },
  {
    title: '药品名称',
    dataIndex: 'name',
    key: 'name',
    width: 150
  },
  {
    title: '规格',
    dataIndex: 'drug_specification',
    key: 'drug_specification',
    width: 120
  },
  {
    title: '库存数量',
    dataIndex: 'stock',
    key: 'stock',
    width: 120,
    sorter: (a, b) => a.stock - b.stock
  },
  {
    title: '库存状态',
    key: 'stockStatus',
    width: 120
  },
  {
    title: '单价(元)',
    dataIndex: 'price',
    key: 'price',
    width: 120,
    sorter: (a, b) => parseFloat(a.price) - parseFloat(b.price)
  },
  {
    title: '生产厂家',
    dataIndex: 'drug_manufacturer',
    key: 'drug_manufacturer',
    width: 150
  },
  {
    title: '操作',
    key: 'actions',
    width: 180
  }
];

const prescriptionColumns = [
  {
    title: '处方ID',
    dataIndex: 'id',
    key: 'id',
    width: 100
  },
  {
    title: '患者信息',
    key: 'patientInfo',
    width: 180
  },
  {
    title: '医生信息',
    key: 'doctorInfo',
    width: 180
  },
  {
    title: '药品信息',
    key: 'drugInfo',
    width: 220
  },
  {
    title: '数量',
    dataIndex: 'total_amount',
    key: 'total_amount',
    width: 80
  },
  {
    title: '已支付数量',
    dataIndex: 'payed_amount',
    key: 'payed_amount'
  },
  {
    title: '已发药数量',
    dataIndex: 'recipe_amount',
    key: 'recipe_amount'
  },
  {
    title: '总价',
    key: 'totalPrice',
    width: 120
  },
  {
    title: '状态',
    key: 'status',
    width: 120
  },
  {
    title: '处方日期',
    dataIndex: 'date',
    key: 'date',
    width: 120
  },
  {
    title: '操作',
    key: 'actions',
    width: 150
  }
];

// 行选择配置
const rowSelection = computed(() => {
  return {
    selectedRowKeys: selectedPrescriptions.value,
    onChange: (selectedRowKeys) => {
      selectedPrescriptions.value = selectedRowKeys;
    },
    getCheckboxProps: (record) => ({
      disabled: record.status_code!== 1 || record.drugStock < parseFloat(record.payed_amount)
    })
  };
});

// 计算属性
const filteredDrugs = computed(() => {
  return drugs.value.filter(drug => 
    drug.name.includes(drugSearchText.value) ||
    drug.id.toString().includes(drugSearchText.value) ||
    drug.manufacturer.includes(drugSearchText.value)
  );
});

const filteredPrescriptions = computed(() => {
  return prescriptions.value.filter(prescription => 
    prescription.patientName.includes(prescriptionSearchText.value) ||
    prescription.doctorName.includes(prescriptionSearchText.value) ||
    prescription.drugName.includes(prescriptionSearchText.value) ||
    prescription.id.toString().includes(prescriptionSearchText.value)
  );
});

// 方法
const handleMenuSelect = ({ key }) => {
  activeTab.value = key;
};

const getStockStatusColor = (stock) => {
  if (stock === 0) return 'red';
  if (stock < 10) return 'orange';
  return 'green';
};

const getStockStatusText = (stock) => {
  if (stock === 0) return '缺货';
  if (stock < 10) return '库存紧张';
  return '库存充足';
};

const getStatusColor = (statusCode) => {
  if (statusCode === 0) return 'blue';
  if (statusCode === 1) return 'orange';
  if (statusCode === 2) return 'green';
  return 'gray';
};

const getStatusText = (statusCode) => {
  if (statusCode === 0) return '开药';
  if (statusCode === 1) return '已付款';
  if (statusCode === 2) return '已付款已发药';
  return '未知状态';
};

const fetchDrugs = async () => {
  try {
    const response = await getDrugs();
    if (response.data.code === 200) {
      drugs.value = response.data.data.map(drug => ({
        id: drug.drug_id,
        name: drug.drug_name,
        price: drug.drug_price,
        stock: drug.storage,
        drug_specification: drug.drug_specification,
        drug_manufacturer: drug.drug_manufacturer
      }));
    } else {
      message.error('获取药品列表失败');
    }
  } catch (error) {
    message.error('获取药品列表失败');
  }
};

const fetchPrescriptions = async () => {
  try {
    const response = await getPrescriptions();
    if (response.data.code === 200) {
      prescriptions.value = response.data.data.map(prescription => {
        const drug = drugs.value.find(d => d.name === prescription.drug_name);
        return {
          id: prescription.pres_id,
          patientId: null, 
          patientName: prescription.pat_name,
          doctorId: null, 
          doctorName: prescription.doc_name,
          drugId: null, 
          drugName: prescription.drug_name,
          drugStock: drug? drug.stock : 0, 
          payed_amount: prescription.payed_amount,
          quantity: prescription.total_amount,
          price: drug? drug.price : 0, 
          recipe_amount: prescription.recipe_amount,
          status_code: prescription.status_code,
          sum_price: prescription.sum_price,
          total_amount: prescription.total_amount,
          date: null 
        };
      });
    } else {
      message.error('获取处方列表失败');
    }
  } catch (error) {
    message.error('获取处方列表失败');
  }
};

const showAddDrugModal = () => {
  addDrugModalVisible.value = true;
};

const resetAddDrugForm = () => {
  Object.assign(addDrugForm, {
    name: '',
    specification: '',
    stock: 0,
    price: 0,
    manufacturer: '',
    category: '',
    description: ''
  });
};

const handleAddDrug = async () => {
  try {
    const formData = new FormData();
    formData.append('drug_name', addDrugForm.name);
    formData.append('price', addDrugForm.price);
    formData.append('stock_quantity', addDrugForm.stock);
    formData.append('drug_specification', addDrugForm.specification);
    formData.append('drug_manufacturer', addDrugForm.manufacturer);


    const response = await addDrugs(formData);
    if (response.data.code === 200) {
      message.success('药品添加成功');
      addDrugModalVisible.value = false;
      resetAddDrugForm();
      await fetchDrugs();
    } else {
      message.error('添加药品失败');
    }
  } catch (error) {
    message.error('添加药品失败');
  }
};

const editDrug = (drug) => {
  currentDrug.value = drug;
  Object.assign(editDrugForm, {
    id: drug.id,
    name: drug.name,
    specification: drug.specification,
    stock: drug.stock,
    price: drug.price,
    manufacturer: drug.manufacturer,
    category: drug.category,
    description: drug.description
  });
  editDrugModalVisible.value = true;
};

const resetEditDrugForm = () => {
  currentDrug.value = null;
  Object.assign(editDrugForm, {
    id: null,
    name: '',
    specification: '',
    stock: 0,
    price: 0,
    manufacturer: '',
    category: '',
    description: ''
  });
};

const handleEditDrug = async () => {
  try {
    const index = drugs.value.findIndex(d => d.id === editDrugForm.id);
    if (index!== -1) {
      drugs.value[index] = {
        ...drugs.value[index],
        ...editDrugForm
      };
      message.success('药品信息更新成功');
      editDrugModalVisible.value = false;
      resetEditDrugForm();
      await fetchDrugs();
    }
  } catch (error) {
    message.error('更新药品信息失败');
  }
};

const deleteDrug = async (id) => {
  try {
    const drug = drugs.value.find(d => d.id === id);
    if (!drug) {
      message.error('未找到该药品');
      return;
    }
    const formData = new FormData();
    formData.append('drug_name', drug.name);

    const response = await deleteDrugs(formData);
    if (response.data.code === 200) {
      message.success('药品删除成功');
      await fetchDrugs();
    } else {
      message.error('删除药品失败');
    }
  } catch (error) {
    message.error('删除药品失败');
  }
};

const showStockModal = (drug) => {
  currentDrug.value = drug;
  stockForm.quantity = 1;
  stockForm.reason = '';
  stockModalVisible.value = true;
};

const resetStockForm = () => {
  currentDrug.value = null;
  stockForm.quantity = 1;
  stockForm.reason = '';
};

const handleStockUpdate = async () => {
  try {
    const formData = new FormData();
    formData.append('drug_id', currentDrug.value.id);
    formData.append('oper_amount', stockForm.quantity);
    formData.append('status_code', 1);

    const response = await updateStore(formData);
    if (response.data.code === 200) {
      message.success(`成功补货 ${stockForm.quantity} 件`);
      stockModalVisible.value = false;
      resetStockForm();
      await fetchDrugs();
      await fetchPrescriptions();
    } else {
      message.error('补货操作失败');
    }
  } catch (error) {
    message.error('补货操作失败');
  }
};

const dispenseMedication = (prescription) => {
  currentPrescription.value = prescription;
  dispenseModalVisible.value = true;
};

const confirmDispense = async () => {
  try {
    const response = await dispensePres(currentPrescription.value.id);
    if (response.data.code === 200) {
      const prescriptionIndex = prescriptions.value.findIndex(p => p.id === currentPrescription.value.id);
      if (prescriptionIndex!== -1) {
        prescriptions.value[prescriptionIndex].status_code = 2;
      }
      
      const drugIndex = drugs.value.findIndex(d => d.name === currentPrescription.value.drugName);
      if (drugIndex!== -1) {
        drugs.value[drugIndex].stock -= parseFloat(currentPrescription.value.payed_amount);
      }
      
      message.success('发药成功');
      dispenseModalVisible.value = false;
      currentPrescription.value = null;
      await fetchDrugs();
      await fetchPrescriptions();
    } else {
      message.error('发药操作失败');
    }
  } catch (error) {
    message.error('发药操作失败');
  }
};

const cancelDispense = () => {
  dispenseModalVisible.value = false;
  currentPrescription.value = null;
};

const batchDispense = async () => {
  try {
    for (const id of selectedPrescriptions.value) {
      const response = await dispensePres(id);
      if (response.data.code === 200) {
        const prescriptionIndex = prescriptions.value.findIndex(p => p.id === id);
        if (prescriptionIndex!== -1) {
          prescriptions.value[prescriptionIndex].status_code = 2;
        }
        
        const prescription = prescriptions.value[prescriptionIndex];
        const drugIndex = drugs.value.findIndex(d => d.name === prescription.drugName);
        if (drugIndex!== -1) {
          drugs.value[drugIndex].stock -= parseFloat(prescription.payed_amount);
        }
      } else {
        message.error(`发药时更新处方 (ID: ${id}) 状态失败`);
        return;
      }
    }
    
    message.success(`成功发放 ${selectedPrescriptions.value.length} 个处方药品`);
    selectedPrescriptions.value = [];
    await fetchDrugs();
    await fetchPrescriptions();
  } catch (error) {
    message.error('批量发药操作失败');
  }
};

const handleDrugSearch = (value) => {
  drugSearchText.value = value;
};

const handlePrescriptionSearch = (value) => {
  prescriptionSearchText.value = value;
};

// 初始化
onMounted(() => {
  fetchDrugs();
  // 确保在获取药品数据后再获取处方数据
  setTimeout(() => {
    fetchPrescriptions();
  }, 500); 
});
</script>

<style scoped>
.drug-management {
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
  margin-left: 200px;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

.drug-card,
.prescription-card {
  top: 50px;
  left: -100px;
  margin-bottom: 24px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.drug-meta {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
}

@media (max-width: 768px) {
  .content {
    padding: 12px;
    margin-left: 0;
  }
}
</style>    