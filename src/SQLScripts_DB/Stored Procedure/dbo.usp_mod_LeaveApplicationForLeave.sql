USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_LeaveApplicationForLeave]    Script Date: 13/09/2021 16:02:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_mod_LeaveApplicationForLeave]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_mod_LeaveApplicationForLeave]

@staffnumber varchar(50),
@startingdate varchar(50) ,
@endingdate varchar(50)
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	--SELECT @under11toil return;
	DECLARE @status varchar(20),@returnstring varchar(100),@strlogin varchar(20)
	
	SET @status = ''success''
	SET @returnstring = ''Leave Application has been done successfully.''
	
	
	
 -- insert new record 
 BEGIN TRANSACTION
	
		
					--update the leave applicatoons table
				SELECT @strlogin=NetLogin from StaffDetails where StaffNumber = @staffnumber
					UPDATE LeaveApplications SET LeaveID = 0 where ID IN (select (ID) from LeaveApplications where dDate >=@startingdate and dDate <=@endingdate and Login=@strlogin and Deleted = 0)
				
					IF  @@ERROR <> 0 
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = ''error''
							SET @returnstring = ''Error saving the Leave Application.''
							RETURN 0
						END
					IF @@ROWCOUNT = 0 
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = ''error''
							SET @returnstring = ''Error saving the Leave Application.''
							RETURN 0
						END
		
		
	SELECT @status strstatus , @returnstring strreturnstring;	
	COMMIT TRANSACTION
						
END



'

EXEC dbo.sp_executesql @strSQL 

GO
