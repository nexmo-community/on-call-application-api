import React, { Component } from 'react'
import { StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native'
import * as SecureStore from 'expo-secure-store'
import { authenticateUser } from '../api/authentication.js'

class LoginScreen extends Component {
  state = {
    email: '',
    password: '',
    errorText: '',
  }

  handleEmail = (email) => this.setState({ email: email});
  handlePassword = (password) => this.setState({ password: password });

  login = () => {
    authenticateUser(this.state.email, this.state.password)
      .then((authenticated) => {
        if (typeof(authenticated.data.token) != "undefined") {
          if (SecureStore.isAvailableAsync()) {
            SecureStore.setItemAsync('Auth', authenticated.data.token)
              .then(() => {
                return this.props.navigation.navigate('Alerts');
              })
              .catch((err) => console.log(err));
          }
        } else {
          this.setState({errorText: 'There was an error: ' + authenticated.message});

          console.log(this.state.errorText);
        }
      })
      .catch((err) => console.log(err));
  }

  render() {
    return (
      <View style={styles.container}>
        <Text style={styles.formLabel}>Login Form</Text>

        <Text style={styles.errorText}>
          {this.state.errorText}
        </Text>

        <TextInput style = {styles.input}
            underlineColorAndroid = "transparent"
            placeholder = "Email"
            placeholderTextColor = "#03A5C9"
            autoCapitalize = "none"
            onChangeText = {this.handleEmail}/>

        <TextInput style = {styles.input}
            underlineColorAndroid = "transparent"
            placeholder = "Password"
            placeholderTextColor = "#03A5C9"
            autoCapitalize = "none"
            onChangeText = {this.handlePassword}
            secureTextEntry = {true} />
            
        <TouchableOpacity
            style = {styles.submitButton}
            onPress = {this.login}>
            <Text style = {styles.submitButtonText}> Sign In </Text>
        </TouchableOpacity>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    height: 50,
  },
  formLabel: {
    fontSize: 20,
    color: '#03A5C9',
  },
  errorText: {
    color: '#8E1600',
    paddingTop: 10,
  },
  input: {
    marginTop: 20,
    width: 300,
    height: 40,
    paddingHorizontal: 10,
    borderRadius: 50,
    borderWidth: 1,
    borderColor: '#03A5C9',
  },
  submitButton: {
    backgroundColor: '#03A5C9',
    width: 300,
    padding: 10,
    margin: 15,
    height: 40,
    borderRadius: 50
  },
  submitButtonText:{
    color: 'white',
    textAlign: 'center'
  },
});

export default LoginScreen;