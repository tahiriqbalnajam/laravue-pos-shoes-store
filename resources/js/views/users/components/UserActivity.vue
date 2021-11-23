<template>
  <el-card v-if="user.name">
    <el-tabs v-model="activeActivity" @tab-click="handleClick">
      <el-tab-pane v-loading="updating" label="Account" name="first">
        <el-form-item label="Name">
          <el-input v-model="user.name" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Email">
          <el-input v-model="user.email" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Password">
          <el-input v-model="user.password" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Roles">
          <el-select v-model="user.roles" multiple placeholder="User Role">
            <el-option
              v-for="role in user_roles"
              :key="role.id"
              :label="role.name"
              :value="role.name"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :disabled="user.role === 'admin'" @click="onSubmit">
            Update
          </el-button>
        </el-form-item>
      </el-tab-pane>
    </el-tabs>
  </el-card>
</template>

<script>
import Resource from '@/api/resource';
import UserResource from '@/api/user';
const userResource = new Resource('users');
const usersR = new UserResource();

export default {
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          name: '',
          email: '',
          password: '',
          avatar: '',
          roles: [],
        };
      },
    },
  },
  data() {
    return {
      activeActivity: 'first',
      carouselImages: [
        'https://cdn.laravue.dev/photo1.png',
        'https://cdn.laravue.dev/photo2.png',
        'https://cdn.laravue.dev/photo3.jpg',
        'https://cdn.laravue.dev/photo4.jpg',
      ],
      updating: false,
      user_roles: [],
    };
  },
  created() {
    this.getRoles();
  },
  methods: {
    handleClick(tab, event) {
      console.log('Switching tab ', tab, event);
    },
    async getRoles() {
      const { data } = await usersR.roles();
      this.user_roles = data;
    },
    onSubmit() {
      this.updating = true;
      userResource
        .update(this.user.id, this.user)
        .then(response => {
          this.updating = false;
          this.$message({
            message: 'User information has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          });
        })
        .catch(error => {
          console.log(error);
          this.updating = false;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.user-activity {
  .user-block {
    .username, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
    }
    :after {
      clear: both;
    }
    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .user-images {
      padding-top: 20px;
    }
  }
  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;
    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }
    .link-black {
      &:hover, &:focus {
        color: #999;
      }
    }
  }
  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}
</style>
