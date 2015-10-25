% Learn a Naive Bayes classifier on the digit dataset, evaluate its
% performance on training and test sets, then visualize the mean and variance
% for each class.

load mnist_train;
load mnist_test;

% Add your code here (it should be less than 10 lines)

[log_prior, class_mean, class_var] = train_nb(train_inputs, train_targets);
[prediction, accuracy] = test_nb(train_inputs, train_targets, log_prior, class_mean, class_var);
fprintf('Training Accuracy = %.6f\n', accuracy*100);
[prediction, accuracy] = test_nb(test_inputs, test_targets, log_prior, class_mean, class_var);
fprintf('Test Accuracy = %.6f\n\n', accuracy*100);
plot_digits(class_mean);
plot_digits(class_var);