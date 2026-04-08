IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = 'DisplayAllocName'
          AND Object_ID = Object_ID(N'[dbo].[LeaveAllocateTypes]'))
BEGIN
ALTER TABLE [dbo].[LeaveAllocateTypes] ADD DisplayAllocName nvarchar(50) default null;
END
 Update LeaveAllocateTypes SET DisplayAllocName= AllocName
 Update LeaveAllocateTypes SET AllocName= 'Comp' Where ID =3

 