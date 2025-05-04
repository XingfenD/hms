import { Form } from 'ant-design-vue';
import { formItemProps } from 'ant-design-vue/es/form';
import axios from 'axios';

// 创建一个 axios 实例
const service = axios.create({
    withCredentials: true, // 允许携带 cookie
    baseURL: 'http://localhost/hms_new/apis', // 根据实际情况修改 API 基础路径
    // baseURL: 'http://121.196.209.50/hms/apis',
    timeout: 5000 // 请求超时时间
});

// 登录方法
export const login = (username, password, role) => {
    const formData = new FormData();
    formData.append('user_type', role);
    formData.append('password', password);
    formData.append('cellphone', username);

    return service.post('/login.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => {
        console.log(response.data);
        return response.data;
    }).catch(error => {
        console.error('登录请求出错:', error);
        return {
            code: 500,
            message: '登录请求出错，请稍后重试'
        };
    });
};



// 注册方法
export const register = (account, username, password, phone, gender, age) => {
    const formData = new FormData();
    formData.append('account', account);
    formData.append('name', username);
    formData.append('password', password);
    formData.append('cellphone', phone);
    formData.append('gender', gender);
    formData.append('age', age);

    return service.post('/register.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => {
        console.log(response.data);
        return response.data;
    }).catch(error => {
        console.error('注册请求出错:', error);
        return {
            code: 500,
            message: '注册请求出错，请稍后重试'
        };
    });
};


// 封装 get_login_info 接口
export const getLoginInfo = () => {
    return service({
        url: '/get_login_info.php',
        method: 'get'
    });
};

// 登出接口
export const loginout = () => {
    return service({
        url: '/logout.php',
        method: 'post'
    }).then(reponse => {
        return reponse.data;
    });
}

//获取所有用户信息
export const get_users_data = () => {
    return service({
        url: '/user_manage/get_users_data.php',
        method: 'get'
    });
}

// 获取单个用户的信息
export const get_single_userinfo_data = (userId) => {
    return service.get("/user_manage/get_single_userinfo.php", {
        params: {
            user_id: userId
        }
    });
} 


// 新增的添加用户的函数
export const add_user = async (formData) => {
    try {
        return service.post('/user_manage/add_user.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then(response => {
            // console.log(response.data);
            return response.data;
        })
    } catch (error) {
        throw error;
    }
};

export const edit_user = (formData) => {
    return service.post('/user_manage/update_user.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

//删除用户的函数
export const delete_user = (userId) => {
    const formData = new FormData();
    formData.append('userId', userId);
    return service.post('/user_manage/delete_user.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => response.data);
}

// 排班api

// 获取科室列表
export const getDepartments = () => {
    return service({
        url: '/schedule_manage/get_deps.php',
        method: 'get'
    }).then(response => {
        return response.data;
    }).catch(error => {
        console.error('获取科室列表失败:', error);
        throw error;
    });
};


export const getDoctorsByDepartment = (departmentId) => {
    return service.get('/schedule_manage/get_doctor_by_department.php', {
        params: {
            department_id: departmentId
        }
    });
};

export const getSchedules = (params) => {
    return service.get('/schedule_manage/get_schedule_by_date.php', {
        params
    });
};

export const addSchedule = (data) => {
    return service.post('/schedule_manage/add_schedule.php', data, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
};

export const updateSchedule = (data) => {
    return service.post('/schedule_manage/update_schedule.php', data, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
};

// 获取指定医生的排班
export const getScheduleByDoctor = (doctor_id) => {
    return service.get('/schedule_manage/get_schedule_by_doctor.php', {
        params: {
            doctor_id: doctor_id
        }
    });
}


//删除排班的函数
// export const deleteSchedule = (scheduleId) => {
//     return service.delete(`/schedule_manage/delete_schedule.php?schedule_id=${scheduleId}`)
//         .then(response => response.data);
// }
export const deleteSchedule = (scheduleId) => {
    const formData = new FormData();
    formData.append("schedule_id", scheduleId);
    return service.post('/schedule_manage/delete_schedule.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }        
    })
}


// 药品管理api

// 获取药品列表
export const getDrugs = () => {
    return service({
        url: '/drug_manage/get_drugs_data.php',
        method: 'get'
    });
}

// 添加药品
export const addDrugs = (formData) => {
    return service.post('/drug_manage/add_drug.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

// 添加库存
export const addStore = (formData) => {
    return service.post('/drug_manage/add_store.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

// 删除药品
export const deleteDrugs = (formData) => {
    return service.post('/drug_manage/delete_drug.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

// 更新库存
export const updateStore = (formData) => {
    return service.post('/drug_manage/change_drug_store.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

//


// 处方api

// 获取所有处方
export const getPrescriptions = () => {
    return service.get('/prescription_manage/get_pending_prescription.php');
}

// 获取指定处方状态
export const getPresStatus = (presId) => {
    return service.get('/prescription_manage/get_pres_status.php', {
        params: {
            pres_id: presId
        }
    });
}

// 添加处方
export const addPrescriptions = (formData) => {
    return service.post('/prescription_manage/add_pres_record.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }        
    });
}

// 添加医嘱和用量
export const addPresResult = (formData) => {
    return service.post('/prescription_manage/add_pres_result.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }          
    });
}

// 更新相应处方的医嘱和用量
export const updatePresResult = (formData) => {
    return service.post('/prescription_manage/update_pres_result.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }           
    });
}

// 获取指定处方的医嘱和用法用量
export const getPresResult = (presId) => {
    return service.get('/prescription_manage/get_pres_result.php', {
        params: {
            pres_id: presId
        }
    });
}

// 给未付费的处方付款 即把处方记录状态由0到1
export const payPres = (presId) => {
    const formData = new FormData();
    formData.append("pres_id", presId);
    return service.post('/prescription_manage/pay_pres_record.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }           
    });
}

// 给已付费的处方发药， 即把处方记录状态由1到2
export const dispensePres = (presId) => {
    const formData = new FormData();
    formData.append("pres_id", presId);
    return service.post('/prescription_manage/dispense_prescription.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }           
    });
} 

// 挂号api

export const getAppoint = (params) => {
    return service.get('/appointment_manage/search_appointment.php', {
        params
    });
}


// 添加挂号
export const addAppoint = (formData) => {
    return service.post('/appointment_manage/create_appointment.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

// 更新挂号状态
export const updateAppointStatus = (formData) => {
    return service.post('/appointment_manage/update_appointment_status.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}


// 添加主诉
export const addAppointinfo = (formData) => {
    return service.post('/appointment_manage/create_ap_info.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });
}

// 查看对应挂号详细信息
export const getAppointinfo = (apId) => {
    return service.get('/appointment_manage/get_ap_info.php', {
        params: {
            ap_id: apId
        }
    });
}

//修改主诉等信息
export const updateAppointinfo = (formData) => {
    return service.post('/appointment_manage/update_ap_info.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    });    
}

// 备份

//创建备份
export const createBackup = () => {
    return service.get('/backup_manage/create_backup.php');
}

//获取备份记录
export const getBackup = () => {
    return service.get('/backup_manage/backup_history.php');
}

// 删除指定备份
export const deleteBackup = (backupFileName) => {
    const formData = new FormData();
    formData.append('backupFileName', backupFileName)
    return service.post('/backup_manage/delete_backup.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }        
    });
}

// 恢复数据库选择对应数据库文件恢复
export const restoreBackup = (backupFileName) => {
    const formData = new FormData();
    formData.append('backupFileName', backupFileName)
    return service.post('/backup_manage/restore_database.php', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }        
    });
}

// 检查项目

// 获取检查项目
export const getCheckup = (params) => {
    return service.get("/examination_manage/search_examination.php", {
        params
    });
}

// 添加检查项目
export const addCheckup = (formData) => {
    return service.post("/examination_manage/create_exam_item.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }           
    });

}

// 更改检查项目
export const updateCheckup = (formData) => {
    return service.post("/examination_manage/update_exam_item.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }           
    });
}

// 删除检查项目
export const deleteCheck = (examDefId) => {
    const formData = new FormData();
    formData.append("exam_def_id", examDefId);
    return service.post("/examination_manage/delete_exam_item.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }           
    });
}

// 获取所有检查记录
export const getExamRecord = () => {
    return service.get("/examination_manage/search_all_exam_status.php");
}

// 查找检查记录
export const getCheckupRecord = (AppointId) => {
    return service.get("/examination_manage/search_exam_record.php", {
        params: {
            ap_id: AppointId
        }
    });
}

// 添加检查记录
export const addCheckupRecord = (formData) => {
    return service.post("/examination_manage/add_exam_record.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }        
    })
}

// 获取指定检查的检查结果
export const getCheckupResult = (examId) => {
    return service.get("/examination_manage/search_exam_result.php", {
        params: {
            exam_id: examId
        }
    });
}

// 患者对检查记录付款即更改检查状态为1
export const payCheckup = (examId) => {
    const formData = new FormData();
    formData.append('exam_id', examId);
    return service.post("/examination_manage/pay_exam_record.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }    
    });
}

// 对检查记录完成检查即更改检查状态为2
export const completeCheckup = (examId) => {
    const formData = new FormData();
    formData.append('exam_id', examId);
    return service.post("/examination_manage/complete_exam_record.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }            
    });
}


// 添加检查结果
export const addCheckupResult = (formData) => {
    return service.post("/examination_manage/add_exam_result.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }            
    });
}

// 更改检查结果
export const updateCheckupResult = (formData) => {
    return service.post("/examination_manage/update_exam_result.php", formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }            
    });
}
