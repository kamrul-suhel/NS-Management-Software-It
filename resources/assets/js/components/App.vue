<template>
    <div id="app">
        <v-app
                dark
                id="inspire"
        >
            <navigation-component v-if="login"></navigation-component>
            <header-component v-if="login"></header-component>
            <v-content>
                <v-container fill-height :class="{'fill-height justify-center align-content-center': !login}">
                    <v-layout >
                        <v-flex>
                            <router-view></router-view>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-content>
        </v-app>
    </div>
</template>

<script>
    import  HeaderComponent  from '../components/layout/HeaderComonent.vue';
    import  NavigationComponent  from '../components/layout/NavigationComponent.vue';
    import  LoginEventBus  from '../event_bus/login-event-bus';

    export default {
        name: 'App',
        components: {
            NavigationComponent,
            HeaderComponent,
        },

        data: () => ({
            login:false
        }),

        props: {
            source: String
        },

        created(){

            axios.get('/islogin').then((response) => {
                if(!response.data.error){
                    this.login = true;

                    let route = this.$route.name;
                    if(route != 'login'){
                        this.$router.push({name: route});
                        return;
                    }
                    this.$router.push({name: 'home'})
                }
            })

            LoginEventBus.$on('successLogin', ()=> {
                this.login = true;
                this.$router.push({name: 'home'})
            });

            LoginEventBus.$on('logoutChangeState', () => {
                this.login = false;
            });
        },
    }
</script>