import * as React from 'react'
import { NavigationContainer } from '@react-navigation/native'
import { createStackNavigator } from '@react-navigation/stack'

import Login from '../components/LoginScreen'
import Alert from '../components/AlertScreen'
import Alerts from '../components/AlertsScreen'

const Stack = createStackNavigator()

function MainStackNavigator() {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName='Login'>
        <Stack.Screen 
          name='Login' 
          component={Login} 
          options={{ title: 'Login Screen', headerShown: false }}
        />
        <Stack.Screen 
          name='Alerts' 
          component={Alerts} 
          options={{ title: 'Alerts Screen' }}
        />
        <Stack.Screen
          name='Alert'
          component={Alert}
          options={({route, navigation}) => (
            {headerTitle: 'Alert Screen', 
            route: {route}, 
            navigation: {navigation}}
        )}
        />
      </Stack.Navigator>
    </NavigationContainer>
  )
}

export default MainStackNavigator;