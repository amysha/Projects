clear
load mnist_train.mat
load mnist_valid.mat

result = [];
total = length(valid_targets);
for k = [1,3,5,7,9]
    correct = 0;
    [valid_labels] = run_knn(k, train_inputs, train_targets, valid_inputs);
    for i = 1:length(valid_labels)
        if valid_labels(i) == valid_targets(i)
            correct = correct + 1;
        end
    end
    rate = correct/total;
    result = [result, rate];
end

result
x = [1,3,5,7,9];
plot(x, result,'*')
axis([0,10,0,1])
title('k-NN Classification Rate on Validation Set')
xlabel('k')
ylabel('Classification Rate')


